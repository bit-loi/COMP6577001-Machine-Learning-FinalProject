import React from 'react';
import ReactDOM from 'react-dom/client';
import { Header1 } from './ui/header';
import { Footer } from './ui/footer';
import ElegantCarousel from './ui/elegant-carousel';
import HeroAscii from './ui/hero-ascii';
import { CollectionFeature } from './ui/feature-section-with-bento-grid';
import '../index.css';

// Mount Header
const headerElement = document.getElementById('react-header');
if (headerElement) {
  ReactDOM.createRoot(headerElement).render(
    <React.StrictMode><Header1 /></React.StrictMode>
  );
}

// Mount Hero (UnicornStudio) — replaces carousel on homepage
const heroElement = document.getElementById('hero-ascii');
if (heroElement) {
  ReactDOM.createRoot(heroElement).render(
    <React.StrictMode><HeroAscii /></React.StrictMode>
  );
}

// Mount Carousel (fallback / other pages)
const carouselElement = document.getElementById('hero-carousel');
if (carouselElement) {
  ReactDOM.createRoot(carouselElement).render(
    <React.StrictMode><ElegantCarousel /></React.StrictMode>
  );
}

// Mount Collection Feature Bento Grid
const collectionFeatureElement = document.getElementById('react-collection-feature');
if (collectionFeatureElement) {
  ReactDOM.createRoot(collectionFeatureElement).render(
    <React.StrictMode><CollectionFeature /></React.StrictMode>
  );
}

// Mount Footer
const footerElement = document.getElementById('react-footer');
if (footerElement) {
  ReactDOM.createRoot(footerElement).render(
    <React.StrictMode><Footer /></React.StrictMode>
  );
}

