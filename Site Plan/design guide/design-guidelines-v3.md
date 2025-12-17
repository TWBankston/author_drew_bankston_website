# Design Guidelines v3 – Cinematic Author Style (Not Tech Sci‑Fi)

This document redefines the visual, animation, and UX direction for the **Drew Bankston author website**, shifting away from a neon “futuristic UI” style and toward a **cinematic, atmospheric, literary sci‑fi aesthetic**.  
It also includes explicit, mandatory instructions for **GSAP** and **Lottie** integration.

---

# 0. DESIGN TONE SHIFT — CORE PRINCIPLE

The site should feel:

### **Cinematic · Atmospheric · Human · Literary**
Not like:
- A sci‑fi operating system  
- A tech dashboard  
- Cyberpunk neon UI  

### Think instead:
- Subtle cosmic textures  
- Soft gradients  
- Emotional, character‑driven sci‑fi themes  
- Book‑cover visual language  
- Film‑title typography  

---

# 1. UPDATED COLOR DIRECTION

Avoid neon blues/purples. Use softer, cinematic tones.

### Background  
- Deep charcoal: `#0d0f12`  
- Midnight navy: `#111522`

### Accents (muted, elegant)  
- Soft lavender highlight: `#c7b8ff`  
- Muted sky: `#b9d7ff`  
- Subtle warm light: low‑opacity peach or gold  

### Gradient usage  
Gradients should be:
- Very low contrast  
- Filmic and slow  
- More like a background “haze” than a UI glow  

---

# 2. TYPOGRAPHY (MORE AUTHORIAL)

### Headings  
Use an elegant serif or serif‑adjacent display font.  
Examples for tone (not required):
- Cormorant  
- Spectral  
- Playfair Display  
- Literata Display  

### Body  
- Inter, Source Sans, or Literata  
- High readability  
- Slightly increased line height  
- Reduced letter spacing for large titles  

### General rules  
- No neon glow around text  
- Titles can have a soft outer vignette or haze  
- Fluid typography (`clamp()`) for all headings  

---

# 3. LAYOUT (EDITORIAL, NOT TECH)

### Hero  
- Off‑center layout (title left or right)  
- Atmospheric background (subtle gradient, noise, or soft cosmic texture)  
- Author name large and cinematic  
- Supporting tagline beneath  
- CTA buttons minimal and calm  

### Section Layouts  
- High whitespace  
- Card panels with glassy/soft blur (but muted)  
- Organic shapes or masked overlays are acceptable  
- Avoid hard geometrical neon UI patterns  

---

# 4. MANDATORY LOTTIE ANIMATION USAGE

Cursor must integrate **at least 3 Lottie animations**.

## 1) Hero Background – Ambient Cosmic Movement  
- Slow, minimal motion  
- Very low opacity  
- Rendered behind hero text  
- Suggestion: small drifting particles or soft swirling cosmic dust  
- *Do not use bright neon shapes*

## 2) Section Divider Animation  
- A thin horizontal Lottie animation between hero and first section  
- Could be a slow-moving constellation line  
- Low contrast, elegant

## 3) CTA or Button Accent  
- Small looped animated arrow or underline  
- Only slight movement  
- Should enhance, not distract  

### Lottie Placement Rules  
- Files stored in: `/theme/drew-bankston/assets/lottie/*.json`  
- All purely decorative animations must be:
  - `aria-hidden="true"`  
  - Disabled when `prefers-reduced-motion: reduce`  

---

# 5. MANDATORY GSAP USAGE

GSAP must be used in at least 3 areas:

## 1) Section Reveal Animations  
- Fade + rise (20px upward)  
- Stagger children for grids (books, events)  
- Ease: `power2.out`  
- Duration: ~0.6–0.8s  

## 2) Hero Parallax  
- Hero title moves slightly on scroll  
- Lottie background animates at a different scroll speed  
- Must be subtle  

## 3) Mobile Navbar Animation  
- GSAP tween to slide down/open the full‑screen mobile menu  
- Fade in menu links one by one  

### Reduced Motion  
If `prefers-reduced-motion: reduce`:  
- Disable parallax  
- Remove staggered motions  
- Use simple opacity reveals only  

---

# 6. COMPONENT DESIGN (UPDATED)

## HERO  
- Cinematic serif title  
- Light haze in background  
- Ambient Lottie animation  
- Soft gradient overlay  
- CTA buttons minimal (solid, understated)

## BOOK CARDS  
- Remove neon blue glow  
- Replace with:
  - Soft elevation shadow  
  - Gentle 1.01 scale on hover  
  - Very subtle border highlight  
- Series chip muted and elegant  

## SERIES PAGE  
- Cinematic banner  
- GSAP‑animated reading order list  
- Background with subtle cosmic texture  

## EVENTS  
- Elegant vertical timeline  
- Date rendered in serif style  
- Soft divider line  
- GSAP reveal on scroll  

## ABOUT PAGE  
- Warm, emotional tone  
- Author portrait lightly desaturated  
- Soft fade‑ins  
- Texture overlay acceptable (film grain, vignette)

---

# 7. APP‑LIKE MOBILE EXPERIENCE

- Sticky bottom CTA on key pages  
- Full‑screen mobile navigation with atmospheric fade backdrop  
- Smooth GSAP easing throughout mobile UI  
- Touch targets min. 44px  
- Mobile card layout must feel intentional & native‑app‑like  

---

# 8. DESIGN TOKENS (UNCHANGED SYSTEM, NEW VALUES)

Still use the tokens.json / tokens.css system, but with **muted cinematic colors** instead of neon sci‑fi.

Key updates:

- Accent colors must be softened  
- Backgrounds lowered in contrast  
- Shadows softened  
- No neon glow tokens  

---

# 9. PERFORMANCE & ACCESSIBILITY

- Lazy load images  
- Preload hero Lottie for smoothness  
- Ensure high contrast readability  
- Avoid fast or high‑motion animations  
- Semantic HTML  

---

# END OF DESIGN GUIDELINES v3
