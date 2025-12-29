<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for video accessibility
 * WCAG 1.2.2 - Captions (Prerecorded) (Level A)
 * WCAG 1.2.5 - Audio Description (Prerecorded) (Level AA)
 */
class VideoAccessibilityCheck extends AbstractCheck {

	/**
	 * Video hosting domains to detect embedded videos
	 */
	private $video_domains = array(
		'youtube.com',
		'youtu.be',
		'youtube-nocookie.com',
		'vimeo.com',
		'player.vimeo.com',
		'dailymotion.com',
		'wistia.com',
		'fast.wistia.net',
		'vidyard.com',
		'play.vidyard.com',
		'loom.com',
		'twitch.tv',
		'player.twitch.tv',
		'facebook.com/plugins/video',
		'tiktok.com',
	);

	public function get_id() {
		return 'video-accessibility';
	}

	public function get_description() {
		return 'Videos must have captions, controls, accessible titles, and should not autoplay.';
	}

	public function get_severity() {
		return 'serious';
	}

	public function get_wcag_criteria() {
		return '1.2.2';
	}

	public function get_wcag_level() {
		return 'A';
	}

	public function get_remediation_hint() {
		return 'Add captions to videos using <track kind="captions">. For embedded videos, enable captions on the platform.';
	}

	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// 1. Check native <video> elements
		$this->check_native_videos( $xpath, $issues );

		// 2. Check embedded videos (iframes)
		$this->check_embedded_videos( $xpath, $issues );

		// 3. Check <audio> elements
		$this->check_audio_elements( $xpath, $issues );

		return $issues;
	}

	/**
	 * Check native <video> elements
	 */
	private function check_native_videos( $xpath, &$issues ) {
		$videos = $xpath->query( '//video' );

		foreach ( $videos as $video ) {
			// Check for controls
			if ( ! $video->hasAttribute( 'controls' ) ) {
				$issues[] = array(
					'element'    => 'video',
					'context'    => $this->get_element_html( $video ),
					'message'    => '<video> element is missing the "controls" attribute. Users must be able to control media playback.',
					'confidence' => 'high',
				);
			}

			// Check for autoplay
			if ( $video->hasAttribute( 'autoplay' ) ) {
				// Check if muted (autoplay with muted is more acceptable)
				$is_muted = $video->hasAttribute( 'muted' );

				$issues[] = array(
					'element'    => 'video',
					'context'    => $this->get_element_html( $video ),
					'message'    => $is_muted
						? '<video> has autoplay enabled (muted). While less disruptive, consider providing pause controls prominently.'
						: '<video> has autoplay enabled with audio. This can be disruptive and disorienting for users.',
					'severity'   => $is_muted ? 'notice' : 'warning',
					'confidence' => 'high',
				);
			}

			// Check for captions/subtitles
			$tracks       = $video->getElementsByTagName( 'track' );
			$has_captions = false;
			$has_descriptions = false;

			foreach ( $tracks as $track ) {
				$kind = strtolower( $track->getAttribute( 'kind' ) );
				if ( $kind === 'captions' || $kind === 'subtitles' ) {
					$has_captions = true;
				}
				if ( $kind === 'descriptions' ) {
					$has_descriptions = true;
				}
			}

			if ( ! $has_captions ) {
				$issues[] = array(
					'element'    => 'video',
					'context'    => $this->get_element_html( $video ),
					'message'    => '<video> is missing caption/subtitle track. Add <track kind="captions" src="captions.vtt"> for deaf/hard-of-hearing users.',
					'confidence' => 'high',
				);
			}

			// Audio descriptions (AAA level - notice)
			if ( ! $has_descriptions ) {
				$issues[] = array(
					'element'    => 'video',
					'context'    => $this->get_element_html( $video ),
					'message'    => '<video> does not have audio descriptions (<track kind="descriptions">). Consider adding for WCAG AAA compliance.',
					'severity'   => 'notice',
					'confidence' => 'medium',
				);
			}

			// Check for accessible name (aria-label or aria-labelledby)
			$has_name = $video->hasAttribute( 'aria-label' ) ||
						$video->hasAttribute( 'aria-labelledby' ) ||
						$video->hasAttribute( 'title' );

			if ( ! $has_name ) {
				$issues[] = array(
					'element'    => 'video',
					'context'    => $this->get_element_html( $video ),
					'message'    => '<video> does not have an accessible name. Add aria-label or title attribute.',
					'severity'   => 'notice',
					'confidence' => 'medium',
				);
			}
		}
	}

	/**
	 * Check embedded videos in iframes
	 */
	private function check_embedded_videos( $xpath, &$issues ) {
		$iframes = $xpath->query( '//iframe' );

		foreach ( $iframes as $iframe ) {
			$src = $iframe->getAttribute( 'src' );

			// Skip if no src
			if ( empty( $src ) ) {
				continue;
			}

			// Check if it's a video embed
			$matched_domain = null;
			foreach ( $this->video_domains as $domain ) {
				if ( stripos( $src, $domain ) !== false ) {
					$matched_domain = $domain;
					break;
				}
			}

			if ( ! $matched_domain ) {
				continue; // Not a video iframe
			}

			// Simplify domain name for display
			$display_domain = preg_replace( '/^(player\.|fast\.|play\.)/', '', $matched_domain );
			$display_domain = preg_replace( '/\.(com|net|tv|io)$/', '', $display_domain );

			// Check for title attribute
			$title = trim( $iframe->getAttribute( 'title' ) );

			if ( empty( $title ) ) {
				$issues[] = array(
					'element'    => 'iframe',
					'context'    => $this->get_element_html( $iframe ),
					'message'    => "Embedded $display_domain video iframe is missing a descriptive title attribute. Screen reader users need this to understand the content.",
					'confidence' => 'high',
				);
			} elseif ( $this->is_generic_title( $title ) ) {
				$issues[] = array(
					'element'    => 'iframe',
					'context'    => $this->get_element_html( $iframe ),
					'message'    => "Embedded video has a generic title: \"$title\". Provide a descriptive title that explains the video content.",
					'severity'   => 'notice',
					'confidence' => 'high',
				);
			}

			// Caption reminder (can't verify programmatically)
			$issues[] = array(
				'element'    => 'iframe',
				'context'    => $this->get_element_html( $iframe ),
				'message'    => "Embedded $display_domain video detected. Verify captions/subtitles are enabled on the video platform.",
				'severity'   => 'notice',
				'confidence' => 'medium',
			);

			// Check for allow="autoplay"
			$allow = $iframe->getAttribute( 'allow' );
			if ( stripos( $allow, 'autoplay' ) !== false ) {
				$issues[] = array(
					'element'    => 'iframe',
					'context'    => $this->get_element_html( $iframe ),
					'message'    => 'Embedded video iframe allows autoplay. Ensure video does not auto-start or provides immediate pause controls.',
					'severity'   => 'notice',
					'confidence' => 'medium',
				);
			}
		}
	}

	/**
	 * Check <audio> elements
	 */
	private function check_audio_elements( $xpath, &$issues ) {
		$audios = $xpath->query( '//audio' );

		foreach ( $audios as $audio ) {
			// Check for controls
			if ( ! $audio->hasAttribute( 'controls' ) ) {
				$issues[] = array(
					'element'    => 'audio',
					'context'    => $this->get_element_html( $audio ),
					'message'    => '<audio> element is missing the "controls" attribute.',
					'confidence' => 'high',
				);
			}

			// Check for autoplay
			if ( $audio->hasAttribute( 'autoplay' ) && ! $audio->hasAttribute( 'muted' ) ) {
				$issues[] = array(
					'element'    => 'audio',
					'context'    => $this->get_element_html( $audio ),
					'message'    => '<audio> has autoplay enabled. This can be disruptive for users.',
					'confidence' => 'high',
				);
			}

			// Transcript reminder
			$issues[] = array(
				'element'    => 'audio',
				'context'    => $this->get_element_html( $audio ),
				'message'    => '<audio> element detected. Ensure a text transcript is available nearby for deaf/hard-of-hearing users.',
				'severity'   => 'notice',
				'confidence' => 'medium',
			);
		}
	}

	/**
	 * Check if title is too generic
	 */
	private function is_generic_title( $title ) {
		$generic_titles = array(
			'video',
			'youtube video',
			'vimeo video',
			'embedded video',
			'video player',
			'media player',
			'iframe',
			'untitled',
		);

		return in_array( strtolower( trim( $title ) ), $generic_titles, true );
	}
}

