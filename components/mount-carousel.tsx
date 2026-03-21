import React from 'react';
import ReactDOM from 'react-dom/client';
import ElegantCarousel from './ui/elegant-carousel';
import '../index.css'; // Assuming this exists or will be handled by Vite

const rootElement = document.getElementById('hero-carousel');
if (rootElement) {
  const root = ReactDOM.createRoot(rootElement);
  root.render(
    <React.StrictMode>
      <ElegantCarousel />
    </React.StrictMode>
  );
}
