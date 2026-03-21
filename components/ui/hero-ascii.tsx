'use client';

import { useEffect } from 'react';

export default function HeroAscii() {
  useEffect(() => {
    const embedScript = document.createElement('script');
    embedScript.type = 'text/javascript';
    embedScript.textContent = `
      !function(){
        if(!window.UnicornStudio){
          window.UnicornStudio={isInitialized:!1};
          var i=document.createElement("script");
          i.src="https://cdn.jsdelivr.net/gh/hiunicornstudio/unicornstudio.js@v1.4.33/dist/unicornStudio.umd.js";
          i.onload=function(){
            window.UnicornStudio.isInitialized||(UnicornStudio.init(),window.UnicornStudio.isInitialized=!0)
          };
          (document.head || document.body).appendChild(i)
        }
      }();
    `;
    document.head.appendChild(embedScript);

    const style = document.createElement('style');
    style.textContent = `
      [data-us-project] { position: relative !important; overflow: hidden !important; }
      [data-us-project] canvas { clip-path: inset(0 0 10% 0) !important; }
      [data-us-project] * { pointer-events: none !important; }
      [data-us-project] a[href*="unicorn"],
      [data-us-project] button[title*="unicorn"],
      [data-us-project] div[title*="Made with"],
      [data-us-project] .unicorn-brand,
      [data-us-project] [class*="brand"],
      [data-us-project] [class*="credit"],
      [data-us-project] [class*="watermark"] {
        display: none !important; visibility: hidden !important; opacity: 0 !important;
        position: absolute !important; left: -9999px !important; top: -9999px !important;
      }
    `;
    document.head.appendChild(style);

    const hideBranding = () => {
      const projectDiv = document.querySelector('[data-us-project]');
      if (projectDiv) {
        const allElements = projectDiv.querySelectorAll('*');
        allElements.forEach(el => {
          const text = (el.textContent || '').toLowerCase();
          if (text.includes('made with') || text.includes('unicorn')) el.remove();
        });
      }
    };

    hideBranding();
    const interval = setInterval(hideBranding, 100);
    setTimeout(hideBranding, 1000);
    setTimeout(hideBranding, 3000);

    return () => {
      clearInterval(interval);
      try { document.head.removeChild(embedScript); document.head.removeChild(style); } catch(e) {}
    };
  }, []);

  return (
    <main className="relative overflow-hidden bg-black" style={{ minHeight: '100vh' }}>
      {/* UnicornStudio animation - desktop only */}
      <div className="absolute inset-0 w-full h-full hidden lg:block">
        <div
          data-us-project="whwOGlfJ5Rz2rHaEUgHl"
          style={{ width: '100%', height: '100%', minHeight: '100vh' }}
        />
      </div>

      {/* Mobile background */}
      <div className="absolute inset-0 lg:hidden" style={{
        backgroundImage: 'radial-gradient(ellipse at center, rgba(255,255,255,0.03) 0%, transparent 70%)',
        background: '#050505'
      }}></div>

      {/* Corner accents */}
      <div className="absolute top-0 left-0 w-12 h-12 border-t-2 border-l-2 z-20" style={{ borderColor: 'rgba(255,255,255,0.2)' }}></div>
      <div className="absolute top-0 right-0 w-12 h-12 border-t-2 border-r-2 z-20" style={{ borderColor: 'rgba(255,255,255,0.2)' }}></div>
      <div className="absolute bottom-0 left-0 w-12 h-12 border-b-2 border-l-2 z-20" style={{ borderColor: 'rgba(255,255,255,0.2)' }}></div>
      <div className="absolute bottom-0 right-0 w-12 h-12 border-b-2 border-r-2 z-20" style={{ borderColor: 'rgba(255,255,255,0.2)' }}></div>

      {/* Content */}
      <div className="relative z-10 flex items-center" style={{ minHeight: '100vh' }}>
        <div className="px-8 lg:px-20 lg:ml-[8%]">
          <div style={{ maxWidth: '520px' }}>
            {/* Eyebrow */}
            <div className="flex items-center gap-3 mb-6" style={{ opacity: 0.5 }}>
              <div style={{ width: '32px', height: '1px', background: 'white' }}></div>
              <span style={{ color: 'white', fontSize: '10px', fontFamily: 'monospace', letterSpacing: '0.2em' }}>PREMEDITATIO MALORUM</span>
              <div style={{ flex: 1, height: '1px', background: 'white' }}></div>
            </div>

            {/* Title */}
            <h1 style={{ fontFamily: 'monospace', fontSize: 'clamp(2rem, 5vw, 3.5rem)', fontWeight: 700, color: 'white', lineHeight: 1.1, letterSpacing: '0.05em', margin: '0 0 24px 0' }}>
              MEMENTO<br />
              <span style={{ opacity: 0.7 }}>MORI.</span>
            </h1>

            {/* Dot row */}
            <div className="hidden lg:flex gap-1 mb-6" style={{ opacity: 0.25 }}>
              {Array.from({ length: 48 }).map((_, i) => (
                <div key={i} style={{ width: '3px', height: '3px', background: 'white', borderRadius: '50%' }}></div>
              ))}
            </div>

            {/* Description */}
            <p style={{ fontFamily: 'monospace', fontSize: '0.875rem', color: 'rgba(255,255,255,0.6)', lineHeight: 1.8, margin: '0 0 36px 0', maxWidth: '380px' }}>
              Curated literature for those who contemplate the inevitable. Books that challenge, endure, and illuminate the human condition.
            </p>

            {/* CTA Buttons */}
            <div className="flex flex-col lg:flex-row gap-3">
              <a href="/bookstore/categories/index.php"
                style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', padding: '10px 24px', background: 'white', color: '#050505', fontFamily: 'monospace', fontSize: '12px', fontWeight: 700, letterSpacing: '0.15em', textDecoration: 'none', transition: 'all 0.2s' }}
                onMouseOver={e => { (e.currentTarget as HTMLAnchorElement).style.background = '#e5e5e5'; }}
                onMouseOut={e => { (e.currentTarget as HTMLAnchorElement).style.background = 'white'; }}>
                EXPLORE COLLECTION
              </a>
              <a href="/bookstore/auth/register.php"
                style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', padding: '10px 24px', background: 'transparent', color: 'white', fontFamily: 'monospace', fontSize: '12px', fontWeight: 700, letterSpacing: '0.15em', textDecoration: 'none', border: '1px solid rgba(255,255,255,0.3)', transition: 'all 0.2s' }}
                onMouseOver={e => { (e.currentTarget as HTMLAnchorElement).style.borderColor = 'white'; }}
                onMouseOut={e => { (e.currentTarget as HTMLAnchorElement).style.borderColor = 'rgba(255,255,255,0.3)'; }}>
                JOIN THE CIRCLE
              </a>
            </div>

            {/* Bottom notation */}
            <div className="hidden lg:flex items-center gap-3 mt-8" style={{ opacity: 0.3 }}>
              <span style={{ color: 'white', fontSize: '10px', fontFamily: 'monospace' }}>∞</span>
              <div style={{ flex: 1, height: '1px', background: 'white' }}></div>
              <span style={{ color: 'white', fontSize: '10px', fontFamily: 'monospace' }}>VITRUVIAN · EST. MMXXV</span>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom status bar */}
      <div className="absolute left-0 right-0 bottom-0 z-20" style={{ borderTop: '1px solid rgba(255,255,255,0.1)', background: 'rgba(0,0,0,0.5)', backdropFilter: 'blur(8px)' }}>
        <div style={{ maxWidth: '1280px', margin: '0 auto', padding: '10px 32px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '24px', fontFamily: 'monospace', fontSize: '9px', color: 'rgba(255,255,255,0.35)' }}>
            <span>SYSTEM.ACTIVE</span>
            <span>V1.0.0</span>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <div style={{ width: '6px', height: '6px', background: 'rgba(255,255,255,0.6)', borderRadius: '50%', animation: 'pulse 2s infinite' }}></div>
            <span style={{ fontFamily: 'monospace', fontSize: '9px', color: 'rgba(255,255,255,0.35)' }}>RENDERING</span>
          </div>
        </div>
      </div>
    </main>
  );
}
