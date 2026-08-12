---
version: alpha
name: Notion Minimal
description: A bright, product-led system with oversized typography, restrained color, and friendly blue accents.
colors:
  primary: "#0075DE"
  secondary: "#E6F3FE"
  tertiary: "#005BAB"
  neutral: "#FFFFFF"
  surface: "#FFFFFF"
  on-surface: "#000000"
  subtle-border: "#0000001A"
  muted-text: "#5B5B5B"
  success: "#1CB344"
  warning: "#FFB000"
  error: "#E5484D"
typography:
  headline-display:
    fontFamily: NotionInter
    fontSize: 81px
    fontWeight: 600
    lineHeight: 85.375px
    letterSpacing: -3.73px
  headline-lg:
    fontFamily: NotionInter
    fontSize: 52px
    fontWeight: 600
    lineHeight: 62px
    letterSpacing: -0.75px
  headline-md:
    fontFamily: NotionInter
    fontSize: 34px
    fontWeight: 400
    lineHeight: 41px
    letterSpacing: 0px
  headline-sm:
    fontFamily: NotionInter
    fontSize: 22px
    fontWeight: 400
    lineHeight: 26px
    letterSpacing: 0px
  body-lg:
    fontFamily: NotionInter
    fontSize: 16px
    fontWeight: 400
    lineHeight: 24px
    letterSpacing: 0px
  body-md:
    fontFamily: NotionInter
    fontSize: 14px
    fontWeight: 400
    lineHeight: 20px
    letterSpacing: 0px
  body-sm:
    fontFamily: NotionInter
    fontSize: 12px
    fontWeight: 400
    lineHeight: 16px
    letterSpacing: 0px
  label-lg:
    fontFamily: NotionInter
    fontSize: 16px
    fontWeight: 500
    lineHeight: 20px
    letterSpacing: 0px
  label-md:
    fontFamily: NotionInter
    fontSize: 14px
    fontWeight: 500
    lineHeight: 20px
    letterSpacing: 0px
  label-sm:
    fontFamily: NotionInter
    fontSize: 12px
    fontWeight: 500
    lineHeight: 16px
    letterSpacing: 0px
  button:
    fontFamily: NotionInter
    fontSize: 16px
    fontWeight: 500
    lineHeight: 1
    letterSpacing: 0px
rounded:
  none: 0px
  sm: 4px
  md: 8px
  lg: 12px
  xl: 24px
  full: 9999px
spacing:
  xs: 8px
  sm: 16px
  md: 32px
  lg: 70px
  xl: 96px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.neutral}"
    typography: "{typography.button}"
    rounded: "{rounded.md}"
    padding: 6px 15px
    height: 38px
  button-primary-hover:
    backgroundColor: "{colors.tertiary}"
    textColor: "{colors.neutral}"
    typography: "{typography.button}"
    rounded: "{rounded.md}"
    padding: 6px 15px
    height: 38px
  button-secondary:
    backgroundColor: "{colors.secondary}"
    textColor: "{colors.tertiary}"
    typography: "{typography.button}"
    rounded: "{rounded.md}"
    padding: 6px 15px
    height: 38px
  button-link:
    backgroundColor: "transparent"
    textColor: "{colors.on-surface}"
    typography: "{typography.label-lg}"
    rounded: "{rounded.none}"
    padding: 0px
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.lg}"
    padding: 24px
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-lg}"
    rounded: "{rounded.md}"
    padding: 10px 12px
---
# Notion Minimal

## Overview
This system feels clean, approachable, and product-forward, with a distinctly modern SaaS tone. The page uses large, confident type, generous white space, and a single vivid blue accent to keep attention on the primary call to action and the product experience. Overall density is low at the hero level, while the embedded app preview introduces controlled detail without making the landing page feel busy.

## Colors
- **Primary (#0075DE):** A bright Notion blue used for the strongest actions, including the main CTA and small emphasis states. It signals trust, clarity, and momentum.
- **Secondary (#E6F3FE):** A pale sky tint used for the secondary button and soft action surfaces. It keeps alternatives visible without competing with the primary blue.
- **Tertiary (#005BAB):** A deeper blue used for text or hover states where stronger contrast is needed. It adds authority while staying within the same brand family.
- **Neutral (#FFFFFF):** The dominant background color, creating a bright editorial canvas and making the content feel open and lightweight.
- **Surface (#FFFFFF):** Card and panel surfaces stay white so the layout reads as layered only through borders, spacing, and shadow restraint.
- **On-surface (#000000):** Pure black is used for headlines and key UI text, giving the brand its sharp, high-contrast look.
- **Subtle-border (#0000001A):** A very light border tone used to separate cards and interfaces without introducing visual heaviness.
- **Muted-text (#5B5B5B):** Used for supporting copy and secondary navigation to reduce emphasis while preserving readability.
- **Success (#1CB344):** A vivid green accent that appears in playful product moments and status cues.
- **Warning (#FFB000):** A warm amber accent for highlights and illustrative details.
- **Error (#E5484D):** A clear red reserved for destructive or alert states.

## Typography
Notion Inter is the core typeface, supported by Inter and system sans fallbacks for consistent rendering. Headlines are bold and compressed through negative letter spacing, especially at display scale, which gives the hero its striking, modern personality. Body text stays simple and highly readable at 14px and 16px, while labels use medium weight to support navigation, buttons, and UI chrome without feeling heavy.

The system does not rely on uppercase for emphasis; instead, it uses weight, size, and spacing to create hierarchy. `headline-display` and `headline-lg` are reserved for marketing statements, `headline-md` and `headline-sm` for section titles or interface headings, and the body styles carry most explanatory content. `label-md` and `label-lg` are the preferred choices for buttons and navigational items.

## Layout
The page is centered and highly controlled, with a fixed content column that keeps the hero text and product mockup aligned on a strong vertical axis. Large spacing steps create clear breathing room between the top navigation, icon row, headline, CTA group, and product preview. The rhythm is intentionally asymmetrical in the details but symmetrical in the composition, which keeps the page energetic while remaining orderly.

Section padding should favor generous top and bottom spacing, with `lg` and `xl` values used for major separations and `sm` to `md` used for local grouping. Cards and embedded surfaces should retain comfortable internal padding around 24px, while buttons and small controls keep compact vertical sizing to avoid visual bulk.

## Elevation & Depth
Depth is subtle rather than dramatic. The system relies on white-on-white surfaces, light borders, and occasional soft shadows to distinguish layers, while most hierarchy comes from scale and contrast rather than heavy elevation. The app preview reads as a framed card with a delicate border and minimal shadow presence, reinforcing a polished but restrained interface.

Avoid stacking multiple shadow levels or adding strong drop shadows to primary surfaces. If depth is needed, prefer tonal separation, hairline borders, and spacing first.

## Shapes
The shape language is soft and restrained. Interactive controls use an 8px radius, cards use a 12px radius, and larger hero embellishments can become more pill-like with the `full` radius. This produces a friendly, product-led feel without drifting into overtly playful skeuomorphism.

Icons and illustrated elements can be circular or rounded to soften the otherwise sharp black typography. Keep corners consistent across UI families so the interface feels cohesive and deliberate.

## Components
Buttons are the most expressive component family. `button-primary` should be filled blue with white text, compact vertical padding, and medium weight text; it is the default action for conversion moments. `button-secondary` should use the pale blue background with deep blue text to indicate a less dominant but still important action. `button-link` is text-only and should be reserved for low-emphasis navigation or inline actions. Button height should stay around 38px, with a minimum comfortable width for short labels.

Cards should be white, bordered lightly with `subtle-border`, and rounded at `rounded.lg`. Padding should sit around 24px to support dense content without crowding. Cards are visually quiet; their job is to organize content, not to decorate it.

Inputs should feel like calm utility surfaces: white background, 8px radius, simple border treatment, and body-sized text. Keep field padding moderate so form controls feel touch-friendly but not oversized. When focused, use the primary blue to signal activity rather than introducing new colors or effects.

Navigation items should remain understated, using body or label text with small spacing and minimal chrome. In the product UI, list items, tabs, and board columns should lean on alignment, subtle background tints, and spacing rather than bold separators. Chips, badges, and status pills may use soft tinted fills with small typography to carry semantic state without drawing too much attention.

## Do's and Don'ts
- Do use large, high-contrast headlines for hero messaging.
- Do keep most surfaces white and separate them with light borders instead of heavy shadows.
- Do reserve the primary blue for key CTAs and important interactive states.
- Do keep button sizing compact and consistent, especially for top-level actions.
- Do use generous whitespace to preserve the calm, premium feel.
- Don't introduce saturated accent colors outside the established blue, green, amber, and red system.
- Don't over-round cards or controls; avoid making the interface look toy-like.
- Don't rely on decorative shadows or gradients to create hierarchy.