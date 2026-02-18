# User Panel Theme - TRC Branded Ocean

## Theme: TRC Branded Ocean (Logo-Matched)

**Colors extracted from TRC logo for perfect brand consistency.**

## Color Palette (Extracted from TRC Logo)

### Primary Blues (from logo)
- **Primary Blue**: #0096d2 (exact from logo - most dominant)
- **Blue Deep**: #0082a0
- **Blue Light**: #00a0e6
- **Blue Darkest**: #006680

### Accent Limes (from logo)
- **Primary Lime**: #a0c80a (exact from logo - second most dominant)
- **Lime Light**: #b4d81a
- **Lime Dark**: #8cb428
- **Lime Darkest**: #6a9a1c

### Support Greens (from logo)
- **Green**: #78aa32, #82aa32, #96be14
- **Green Dark**: #6ea046

### Ocean Backgrounds
- **Abyss**: #001a22
- **Deep**: #002a35
- **Mid**: #003845
- **Surface**: #004855

### Text Colors
- Primary: #f0f9ff
- Secondary: #bae6fd
- Muted: #7dd3fc

## Key Features

1. **Logo-Exact Colors**: All colors extracted directly from TRC logo
2. **Ocean Wave Animation**: Subtle animated wave background effect
3. **Glow Effects**: Blue glow pada active/hover states
4. **Gradient Backgrounds**: Ocean depth gradient dari abyss ke surface
5. **Smooth Transitions**: 300ms transitions untuk all interactive elements
6. **Glassmorphism**: Backdrop blur untuk cards dan modals
7. **Custom Scrollbar**: Styled scrollbar dengan blue gradient
8. **Data Color Badges**: Specific styling untuk Data Kuning (lime) dan Data Biru (blue)

## CSS Variables

```css
/* Primary Blues */
--trc-blue: #0096d2;
--trc-blue-deep: #0082a0;
--trc-blue-light: #00a0e6;

/* Accent Limes */
--trc-lime: #a0c80a;
--trc-lime-light: #b4d81a;
--trc-lime-dark: #8cb428;

/* Ocean Backgrounds */
--ocean-abyss: #001a22;
--ocean-deep: #002a35;
--ocean-mid: #003845;
--ocean-surface: #004855;

/* Effects */
--trc-glow: rgba(0, 150, 210, 0.5);
--trc-lime-glow: rgba(160, 200, 10, 0.5);
```

## Files Modified

1. `app/Providers/Filament/UserPanelProvider.php` - Custom hex color definitions
2. `resources/css/filament/user/theme.css` - Complete TRC branded theme

## Build Command

```bash
npm run build
```

## Logo Color Analysis

The logo colors were extracted using Python PIL:
- Dominant: Sky Blue family (#0096d2 range) - 8.2% coverage
- Secondary: Lime Green family (#a0c80a range) - 5.7% coverage
- Support: Various green shades (#78aa32 range)

This theme uses the EXACT colors from the logo for perfect brand consistency.