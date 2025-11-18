# 🎨 Guide de Design - ComptaPro SaaS

## Vue d'ensemble

ComptaPro dispose d'un système de design moderne et cohérent avec des animations fluides, des composants réutilisables et une interface élégante.

---

## 🎯 Principes de Design

### 1. **Moderne & Élégant**
- Border-radius généreux (8px à 16px)
- Gradients vibrants
- Ombres subtiles avec profondeur
- Transitions fluides

### 2. **Performance**
- Animations CSS pure (pas de JS)
- Transitions optimisées (cubic-bezier)
- Lazy loading des composants
- Images et assets optimisés

### 3. **Accessibilité**
- Contraste suffisant (WCAG AA)
- Navigation au clavier
- Labels ARIA
- Focus visible

### 4. **Responsive**
- Mobile-first
- Breakpoints standards (576px, 768px, 992px, 1200px)
- Grids flexibles
- Images adaptatives

---

## 🎨 Système de Couleurs

### Couleurs Principales

```css
/* Primary (Bleu) */
--color-primary: #3b82f6;
--color-primary-dark: #2563eb;
--color-primary-light: #60a5fa;

/* Success (Vert) */
--color-success: #10b981;
--color-success-dark: #059669;
--color-success-light: #34d399;

/* Warning (Orange) */
--color-warning: #f59e0b;
--color-warning-dark: #d97706;
--color-warning-light: #fbbf24;

/* Danger (Rouge) */
--color-danger: #ef4444;
--color-danger-dark: #dc2626;
--color-danger-light: #f87171;

/* Gray Scale */
--gray-50: #f9fafb;
--gray-100: #f3f4f6;
--gray-200: #e5e7eb;
--gray-300: #d1d5db;
--gray-400: #9ca3af;
--gray-500: #6b7280;
--gray-600: #4b5563;
--gray-700: #374151;
--gray-800: #1f2937;
--gray-900: #111827;
```

### Gradients

```css
/* Purple Gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Pink Gradient */
background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);

/* Cyan Gradient */
background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);

/* Yellow Gradient */
background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
```

---

## 📐 Espacements & Dimensions

### Spacing Scale

```css
4px   → gap, small padding
8px   → buttons, badges
12px  → cards internal
16px  → standard padding
20px  → sections
24px  → large cards
32px  → page sections
40px  → hero sections
```

### Border Radius

```css
4px   → small elements (badges, chips)
8px   → buttons, inputs
12px  → cards, alerts
16px  → modals, large cards
20px  → badges circulaires
50%   → avatars, dots
```

### Shadows

```css
/* Card Shadow */
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);

/* Card Hover */
box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);

/* Dropdown */
box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);

/* Floating */
box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
```

---

## 🎭 Composants UI

### Modal

```vue
<Modal :show="showModal" @close="showModal = false" title="Mon Modal" size="md">
    <p>Contenu du modal</p>
    
    <template #footer>
        <button @click="showModal = false">Annuler</button>
        <button @click="save">Enregistrer</button>
    </template>
</Modal>
```

**Props:**
- `show` (Boolean) - Afficher/masquer
- `title` (String) - Titre du modal
- `size` (String) - sm, md, lg, xl
- `closable` (Boolean) - Fermeture possible

### Alert

```vue
<Alert 
    variant="success" 
    title="Succès !" 
    message="Opération réussie"
    :auto-close="3000"
/>
```

**Variants:** success, info, warning, danger

### Card

```vue
<Card variant="gradient" :hover="true" title="Ma Card">
    <p>Contenu de la card</p>
    
    <template #footer>
        <button>Action</button>
    </template>
</Card>
```

**Variants:** default, gradient, bordered, elevated

### Loading

```vue
<Loading type="spinner" variant="primary" size="md" text="Chargement..." />
```

**Types:** spinner, dots, pulse, bars

---

## ✨ Animations

### Classes Utilitaires

```html
<!-- Fade In -->
<div class="animate-fadeIn">Contenu</div>

<!-- Fade In Up -->
<div class="animate-fadeInUp">Contenu</div>

<!-- Scale In -->
<div class="animate-scaleIn">Contenu</div>

<!-- With Delay -->
<div class="animate-fadeIn animate-delay-200">Contenu</div>
```

### Hover Effects

```html
<!-- Lift on Hover -->
<div class="hover-lift">Card</div>

<!-- Scale on Hover -->
<div class="hover-scale">Button</div>

<!-- Glow on Hover -->
<div class="hover-glow">Input</div>

<!-- Gradient Shine -->
<div class="hover-gradient">Banner</div>
```

### Skeleton Loading

```html
<div class="skeleton skeleton-title"></div>
<div class="skeleton skeleton-text"></div>
<div class="skeleton skeleton-text"></div>
```

### Stagger Lists

```html
<div v-for="item in items" class="stagger-item">
    {{ item.name }}
</div>
```

---

## 🎨 Layout

### Sidebar

- **Width:** 260px (expanded) → 70px (collapsed)
- **Background:** Gradient sombre (#1e293b → #0f172a)
- **Transition:** 0.3s cubic-bezier
- **Z-index:** 1040
- **État:** Persistant (localStorage)

### Header

- **Height:** 70px
- **Position:** Sticky top
- **Background:** White
- **Z-index:** 1030
- **Shadow:** 0 2px 8px rgba(0, 0, 0, 0.05)

### Content

- **Background:** #f8f9fa (light mode)
- **Background:** #111827 (dark mode)
- **Padding:** 24px
- **Min-height:** 100vh

---

## 🌙 Dark Mode

### Activation

```javascript
const darkMode = ref(localStorage.getItem('dark-mode') === 'true');

const toggleTheme = () => {
    darkMode.value = !darkMode.value;
    localStorage.setItem('dark-mode', darkMode.value);
    document.documentElement.setAttribute('data-theme', darkMode.value ? 'dark' : 'light');
};
```

### Styles

```css
[data-theme="dark"] {
    --bg-main: #111827;
    --text-primary: #f9fafb;
    --border-color: #374151;
}

[data-theme="dark"] .card {
    background: #1f2937;
    border-color: #374151;
}
```

---

## 📊 Dashboard

### Stats Cards

- **Grid:** repeat(auto-fit, minmax(280px, 1fr))
- **Gap:** 24px
- **Hover:** translateY(-8px)
- **Icons:** Gradient backgrounds (56px × 56px)

### Charts

- **Circular Progress:** SVG avec stroke-dashoffset
- **Status Bars:** Width animé (0.5s ease)
- **Colors:** Color-coded par statut

### Activity Cards

- **Max-height:** 400px
- **Overflow:** scroll
- **Avatars:** Gradient backgrounds (48px circle)

---

## 🎯 Best Practices

### Transitions

```css
/* Standard */
transition: all 0.3s ease;

/* Fast */
transition: all 0.15s ease;

/* Slow */
transition: all 0.5s ease;

/* Smooth */
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
```

### Hover States

```css
.element {
    transition: all 0.3s;
}

.element:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}
```

### Loading States

```vue
<div v-if="loading">
    <Loading type="spinner" variant="primary" />
</div>
<div v-else>
    <!-- Content -->
</div>
```

### Error States

```vue
<Alert 
    v-if="error" 
    variant="danger" 
    :title="error.title"
    :message="error.message"
    @close="error = null"
/>
```

---

## 🚀 Performance

### Optimisations

1. **CSS Animations** : Préférer CSS à JavaScript
2. **Transform/Opacity** : Utiliser pour animations (GPU)
3. **Will-change** : Pour animations complexes
4. **Lazy Loading** : Composants et images
5. **Debounce** : Pour events fréquents (scroll, resize)

### Code Splitting

```javascript
// Lazy load components
const HeavyComponent = defineAsyncComponent(() => 
    import('./components/HeavyComponent.vue')
);
```

---

## 📱 Responsive

### Breakpoints

```css
/* Mobile First */
@media (min-width: 576px) { /* Small */ }
@media (min-width: 768px) { /* Medium */ }
@media (min-width: 992px) { /* Large */ }
@media (min-width: 1200px) { /* XL */ }
```

### Mobile Adaptations

- Sidebar → Overlay
- Search bar → Hidden
- Stats grid → 1 column
- Actions → 2 columns
- Font sizes → Légèrement réduits

---

## 🎨 Exemples d'Utilisation

### Page avec Card

```vue
<template>
    <div class="page-container animate-fadeIn">
        <Card title="Titre" variant="default" :hover="true">
            <p>Contenu</p>
            
            <template #footer>
                <button class="btn btn-primary">Action</button>
            </template>
        </Card>
    </div>
</template>

<style scoped>
.page-container {
    padding: 24px;
    max-width: 1200px;
    margin: 0 auto;
}
</style>
```

### Liste avec Stagger

```vue
<template>
    <div class="list-container">
        <div v-for="(item, index) in items" :key="item.id" 
             class="list-item stagger-item hover-lift">
            {{ item.name }}
        </div>
    </div>
</template>
```

### Modal avec Loading

```vue
<template>
    <Modal :show="showModal" @close="showModal = false" title="Chargement">
        <Loading v-if="loading" type="dots" variant="primary" />
        <div v-else>
            <!-- Content -->
        </div>
    </Modal>
</template>
```

---

## 📚 Resources

- **Bootstrap Icons:** https://icons.getbootstrap.com/
- **Gradient Generator:** https://cssgradient.io/
- **Color Palette:** https://tailwindcss.com/docs/customizing-colors
- **Animation Library:** https://animate.style/

---

## ✅ Checklist Design

Avant de déployer une nouvelle feature :

- [ ] Responsive mobile/tablet/desktop testé
- [ ] Dark mode compatible
- [ ] Animations fluides
- [ ] Loading states implémentés
- [ ] Error states gérés
- [ ] Accessibility (ARIA, keyboard)
- [ ] Cross-browser testé
- [ ] Performance optimisée

---

**ComptaPro Design System v1.0.0**
*Modern, Elegant, Professional* 🎨✨
