<?php
/**
 * YouTube embed processor class
 *
 * Handles YouTube URL detection and responsive embedding with
 * automatic aspect ratio detection for regular videos vs Shorts.
 *
 * @package    Yansir_MD
 * @since      1.0.0
 * @license    GPL-3.0+
 */
class Yansir_MD_YouTube {

    /**
     * Placeholder format for protected URLs
     * Using a format that won't be modified by Parsedown
     */
    private $placeholder_prefix = 'YANSIRYT';
    private $placeholder_suffix = 'ENDYT';

    /**
     * Store extracted YouTube data
     */
    private $youtube_data = array();

    /**
     * YouTube URL patterns - simplified version
     *
     * We'll use a different approach: match all YouTube URLs first,
     * then check context in the callback to skip those inside markdown links.
     */
    private $patterns = array(
        // YouTube Shorts: youtube.com/shorts/VIDEO_ID
        'shorts' => '/https?:\/\/(?:www\.)?youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})(?:\?[^\s\)]*)?/',
        // Standard watch URL: youtube.com/watch?v=VIDEO_ID
        'watch' => '/https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})(?:&[^\s\)]*)?/',
        // Short URL: youtu.be/VIDEO_ID
        'short' => '/https?:\/\/youtu\.be\/([a-zA-Z0-9_-]{11})(?:\?[^\s\)]*)?/',
        // Embed URL: youtube.com/embed/VIDEO_ID
        'embed' => '/https?:\/\/(?:www\.)?youtube\.com\/embed\/([a-zA-Z0-9_-]{11})(?:\?[^\s\)]*)?/',
    );

    /**
     * Pre-process markdown to protect YouTube URLs from Parsedown
     *
     * Only converts standalone URLs (not inside markdown links)
     *
     * @param string $markdown The markdown content
     * @return string Modified markdown with placeholders
     */
    public function preprocess($markdown) {
        $this->youtube_data = array();
        $index = 0;

        // Collect all matches with their positions
        $all_matches = array();

        foreach ($this->patterns as $type => $pattern) {
            if (preg_match_all($pattern, $markdown, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $i => $match) {
                    $all_matches[] = array(
                        'full_match' => $match[0],
                        'offset' => $match[1],
                        'video_id' => $matches[1][$i][0],
                        'type' => $type,
                    );
                }
            }
        }

        // Sort by offset descending (replace from end to avoid offset shifts)
        usort($all_matches, function($a, $b) {
            return $b['offset'] - $a['offset'];
        });

        // Process each match
        foreach ($all_matches as $match) {
            $offset = $match['offset'];
            $full_match = $match['full_match'];
            $match_len = strlen($full_match);

            // Check if URL is inside a markdown link: [text](url) or bare link after ]
            // Look at character before the URL
            $char_before = $offset > 0 ? $markdown[$offset - 1] : '';

            // Skip if preceded by ( or [ (inside markdown link syntax)
            if ($char_before === '(' || $char_before === '[') {
                continue;
            }

            // Check if followed by ) (closing of markdown link)
            $char_after_pos = $offset + $match_len;
            $char_after = $char_after_pos < strlen($markdown) ? $markdown[$char_after_pos] : '';
            if ($char_after === ')') {
                continue;
            }

            // This is a standalone URL, replace with placeholder
            $is_shorts = ($match['type'] === 'shorts');

            $this->youtube_data[$index] = array(
                'video_id' => $match['video_id'],
                'is_shorts' => $is_shorts,
                'original_url' => $full_match,
            );

            $placeholder = $this->placeholder_prefix . $index . $this->placeholder_suffix;
            $index++;

            // Replace in markdown
            $markdown = substr_replace($markdown, $placeholder, $offset, $match_len);
        }

        return $markdown;
    }

    /**
     * Post-process HTML to replace placeholders with responsive embeds
     *
     * @param string $html The parsed HTML content
     * @return string HTML with YouTube embeds
     */
    public function postprocess($html) {
        if (empty($this->youtube_data)) {
            return $html;
        }

        foreach ($this->youtube_data as $index => $data) {
            $placeholder = $this->placeholder_prefix . $index . $this->placeholder_suffix;

            // Generate responsive embed
            $embed = $this->generate_embed($data['video_id'], $data['is_shorts']);

            // Parsedown wraps standalone text in <p> tags, which creates invalid HTML
            // when we replace with a <div>. Handle this case first.
            $wrapped_placeholder = '<p>' . $placeholder . '</p>';
            if (strpos($html, $wrapped_placeholder) !== false) {
                $html = str_replace($wrapped_placeholder, $embed, $html);
            } else {
                // Replace bare placeholder
                $html = str_replace($placeholder, $embed, $html);
            }
        }

        return $html;
    }

    /**
     * Generate responsive YouTube embed HTML
     *
     * @param string $video_id The YouTube video ID
     * @param bool $is_shorts Whether this is a Shorts video
     * @return string The embed HTML
     */
    private function generate_embed($video_id, $is_shorts = false) {
        $video_id = esc_attr($video_id);

        // Use different wrapper class for different aspect ratios
        $wrapper_class = $is_shorts ? 'yansir-yt-shorts' : 'yansir-yt-video';

        // Build iframe URL with privacy-enhanced mode
        $embed_url = "https://www.youtube-nocookie.com/embed/{$video_id}";

        // For shorts, we might want to disable some controls
        if ($is_shorts) {
            $embed_url .= '?rel=0&modestbranding=1';
        }

        $html = sprintf(
            '<div class="yansir-yt-wrapper %s">' .
            '<iframe src="%s" ' .
            'frameborder="0" ' .
            'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" ' .
            'referrerpolicy="strict-origin-when-cross-origin" ' .
            'allowfullscreen></iframe>' .
            '</div>',
            esc_attr($wrapper_class),
            esc_url($embed_url)
        );

        return $html;
    }

}
