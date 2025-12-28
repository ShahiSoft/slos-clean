<?php
/**
 * Color Themes Configuration
 *
 * Define available color themes for ShahiTemplate admin interface.
 * Each theme includes CSS variables for backgrounds, accents, text, and effects.
 *
 * @package    ShahiTemplate
 * @subpackage Config
 * @since      1.0.0
 */

return [
    'neon-aether' => [
        'name' => 'Neon Aether',
        'description' => 'Electric cyan and violet on dark indigo - the signature futuristic look',
        'preview_colors' => ['#00d4ff', '#7c3aed', '#0a0e27'],
        'variables' => [
            // Backgrounds
            '--shahi-bg-primary' => '#0a0e27',
            '--shahi-bg-secondary' => '#141834',
            '--shahi-bg-tertiary' => '#1e2542',
            '--shahi-bg-elevated' => '#252d50',
            
            // Accents
            '--shahi-accent-primary' => '#00d4ff',
            '--shahi-accent-secondary' => '#7c3aed',
            '--shahi-accent-tertiary' => '#f59e0b',
            '--shahi-accent-success' => '#10b981',
            '--shahi-accent-warning' => '#f59e0b',
            '--shahi-accent-error' => '#ef4444',
            
            // Gradients
            '--shahi-gradient-primary' => 'linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%)',
            '--shahi-gradient-secondary' => 'linear-gradient(135deg, #7c3aed 0%, #ec4899 100%)',
            '--shahi-gradient-success' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
            
            // Text
            '--shahi-text-primary' => '#ffffff',
            '--shahi-text-secondary' => '#a8b2d1',
            '--shahi-text-muted' => '#6b7ba0',
            '--shahi-text-accent' => '#00d4ff',
            
            // Borders
            '--shahi-border-color' => '#2d3561',
            '--shahi-border-light' => '#3d4570',
            '--shahi-border-accent' => '#00d4ff',
        ],
    ],
    
    'verdant-brass' => [
        'name' => 'Verdant Brass',
        'description' => 'Deep olive and warm mustard with brass accents - earthy sophistication',
        'preview_colors' => ['#d4af37', '#556b2f', '#1a1f16'],
        'variables' => [
            // Backgrounds
            '--shahi-bg-primary' => '#1a1f16',
            '--shahi-bg-secondary' => '#242a1f',
            '--shahi-bg-tertiary' => '#2f3628',
            '--shahi-bg-elevated' => '#3a4132',
            
            // Accents
            '--shahi-accent-primary' => '#d4af37',
            '--shahi-accent-secondary' => '#556b2f',
            '--shahi-accent-tertiary' => '#daa520',
            '--shahi-accent-success' => '#6b8e23',
            '--shahi-accent-warning' => '#daa520',
            '--shahi-accent-error' => '#cd5c5c',
            
            // Gradients
            '--shahi-gradient-primary' => 'linear-gradient(135deg, #d4af37 0%, #556b2f 100%)',
            '--shahi-gradient-secondary' => 'linear-gradient(135deg, #556b2f 0%, #3d5229 100%)',
            '--shahi-gradient-success' => 'linear-gradient(135deg, #6b8e23 0%, #556b2f 100%)',
            
            // Text
            '--shahi-text-primary' => '#f5f5dc',
            '--shahi-text-secondary' => '#c4c8b0',
            '--shahi-text-muted' => '#8b8f7a',
            '--shahi-text-accent' => '#d4af37',
            
            // Borders
            '--shahi-border-color' => '#3a4132',
            '--shahi-border-light' => '#4a5240',
            '--shahi-border-accent' => '#d4af37',
        ],
    ],
    
    'sienna-ember' => [
        'name' => 'Sienna Ember',
        'description' => 'Burnt orange and maroon on charcoal - warm industrial energy',
        'preview_colors' => ['#ff6347', '#800000', '#1c1c1c'],
        'variables' => [
            // Backgrounds
            '--shahi-bg-primary' => '#1c1c1c',
            '--shahi-bg-secondary' => '#2a1f1f',
            '--shahi-bg-tertiary' => '#3a2626',
            '--shahi-bg-elevated' => '#4a3030',
            
            // Accents
            '--shahi-accent-primary' => '#ff6347',
            '--shahi-accent-secondary' => '#800000',
            '--shahi-accent-tertiary' => '#cd853f',
            '--shahi-accent-success' => '#ff8c42',
            '--shahi-accent-warning' => '#ff8c42',
            '--shahi-accent-error' => '#dc143c',
            
            // Gradients
            '--shahi-gradient-primary' => 'linear-gradient(135deg, #ff6347 0%, #800000 100%)',
            '--shahi-gradient-secondary' => 'linear-gradient(135deg, #800000 0%, #5c0000 100%)',
            '--shahi-gradient-success' => 'linear-gradient(135deg, #ff8c42 0%, #ff6347 100%)',
            
            // Text
            '--shahi-text-primary' => '#ffffff',
            '--shahi-text-secondary' => '#d4b5a8',
            '--shahi-text-muted' => '#9d8274',
            '--shahi-text-accent' => '#ff6347',
            
            // Borders
            '--shahi-border-color' => '#4a3030',
            '--shahi-border-light' => '#5a3838',
            '--shahi-border-accent' => '#ff6347',
        ],
    ],
    
    'aurora-bloom' => [
        'name' => 'Aurora Bloom',
        'description' => 'Magenta and purple on midnight plum - vibrant cosmic beauty',
        'preview_colors' => ['#ff1493', '#9370db', '#1a0f1f'],
        'variables' => [
            // Backgrounds
            '--shahi-bg-primary' => '#1a0f1f',
            '--shahi-bg-secondary' => '#251829',
            '--shahi-bg-tertiary' => '#332238',
            '--shahi-bg-elevated' => '#402d47',
            
            // Accents
            '--shahi-accent-primary' => '#ff1493',
            '--shahi-accent-secondary' => '#9370db',
            '--shahi-accent-tertiary' => '#da70d6',
            '--shahi-accent-success' => '#ba55d3',
            '--shahi-accent-warning' => '#da70d6',
            '--shahi-accent-error' => '#ff69b4',
            
            // Gradients
            '--shahi-gradient-primary' => 'linear-gradient(135deg, #ff1493 0%, #9370db 100%)',
            '--shahi-gradient-secondary' => 'linear-gradient(135deg, #9370db 0%, #8a2be2 100%)',
            '--shahi-gradient-success' => 'linear-gradient(135deg, #ba55d3 0%, #9370db 100%)',
            
            // Text
            '--shahi-text-primary' => '#ffffff',
            '--shahi-text-secondary' => '#e6d5f0',
            '--shahi-text-muted' => '#b99cc7',
            '--shahi-text-accent' => '#ff1493',
            
            // Borders
            '--shahi-border-color' => '#402d47',
            '--shahi-border-light' => '#503d57',
            '--shahi-border-accent' => '#ff1493',
        ],
    ],
    
    'graphite-veil' => [
        'name' => 'Graphite Veil',
        'description' => 'Shades of grey with silver accents - minimalist elegance',
        'preview_colors' => ['#c0c0c0', '#696969', '#1a1a1a'],
        'variables' => [
            // Backgrounds
            '--shahi-bg-primary' => '#1a1a1a',
            '--shahi-bg-secondary' => '#242424',
            '--shahi-bg-tertiary' => '#2e2e2e',
            '--shahi-bg-elevated' => '#383838',
            
            // Accents
            '--shahi-accent-primary' => '#c0c0c0',
            '--shahi-accent-secondary' => '#696969',
            '--shahi-accent-tertiary' => '#a9a9a9',
            '--shahi-accent-success' => '#90ee90',
            '--shahi-accent-warning' => '#ffd700',
            '--shahi-accent-error' => '#ff6b6b',
            
            // Gradients
            '--shahi-gradient-primary' => 'linear-gradient(135deg, #c0c0c0 0%, #696969 100%)',
            '--shahi-gradient-secondary' => 'linear-gradient(135deg, #696969 0%, #505050 100%)',
            '--shahi-gradient-success' => 'linear-gradient(135deg, #90ee90 0%, #7dda7d 100%)',
            
            // Text
            '--shahi-text-primary' => '#ffffff',
            '--shahi-text-secondary' => '#d3d3d3',
            '--shahi-text-muted' => '#909090',
            '--shahi-text-accent' => '#c0c0c0',
            
            // Borders
            '--shahi-border-color' => '#383838',
            '--shahi-border-light' => '#484848',
            '--shahi-border-accent' => '#c0c0c0',
        ],
    ],

    'mac-slate-liquid' => [
        'name' => 'Mac Slate Liquid',
        'description' => 'Soft slate tones with subtle gradients and liquid glass feel',
        'preview_colors' => ['#0f172a', '#334155', '#60a5fa'],
        'variables' => [
            // Backgrounds (macOS-like slate)
            '--shahi-bg-primary'   => '#0f172a',  // Slate 900
            '--shahi-bg-secondary' => '#111827',  // Gray 900
            '--shahi-bg-tertiary'  => '#1f2937',  // Gray 800
            '--shahi-bg-elevated'  => 'rgba(31, 41, 55, 0.6)', // Glass layer

            // Accents (subtle blues with Apple-like feel)
            '--shahi-accent-primary'   => '#60a5fa', // Blue 400
            '--shahi-accent-secondary' => '#93c5fd', // Blue 300
            '--shahi-accent-tertiary'  => '#38bdf8', // Sky 400
            '--shahi-accent-success'   => '#34d399', // Green 400
            '--shahi-accent-warning'   => '#f59e0b', // Amber 500
            '--shahi-accent-error'     => '#ef4444', // Red 500

            // Gradients (liquid/glass subtle)
            '--shahi-gradient-primary'   => 'linear-gradient(135deg, rgba(96,165,250,0.35) 0%, rgba(147,197,253,0.25) 100%)',
            '--shahi-gradient-secondary' => 'linear-gradient(135deg, rgba(56,189,248,0.30) 0%, rgba(147,197,253,0.20) 100%)',
            '--shahi-gradient-success'   => 'linear-gradient(135deg, rgba(52,211,153,0.30) 0%, rgba(16,185,129,0.20) 100%)',

            // Text
            '--shahi-text-primary'   => '#e5e7eb', // Gray 200
            '--shahi-text-secondary' => '#cbd5e1', // Slate 300
            '--shahi-text-muted'     => '#94a3b8', // Slate 400
            '--shahi-text-accent'    => '#93c5fd', // Blue 300

            // Borders & effects
            '--shahi-border-color'  => 'rgba(148,163,184,0.25)',
            '--shahi-border-light'  => 'rgba(148,163,184,0.15)',
            '--shahi-border-accent' => '#93c5fd',
            '--shahi-shadow'        => '0 8px 24px rgba(2, 6, 23, 0.35)',
            '--shahi-shadow-lg'     => '0 16px 40px rgba(2, 6, 23, 0.45)',
            '--shahi-radius'        => '12px',
            '--shahi-radius-sm'     => '8px',
            '--shahi-transition'    => '0.25s cubic-bezier(0.4, 0, 0.2, 1)',
        ],
    ],
];

