'use client';
import React, { useState, useEffect, useRef, useCallback } from 'react';
import { ChevronLeft, ChevronRight, BookOpen, Sparkles, Library, Compass } from 'lucide-react';

interface SlideData {
  title: string;
  subtitle: string;
  description: string;
  accent: string;
  imageUrl: string;
  icon: React.ReactNode;
}

const slides: SlideData[] = [
  {
    title: 'Whispers of Old Souls',
    subtitle: 'Rare Classics Collection',
    description: 'Step into the dusty corridors of history. From hand-bound first editions to timeless prose that shaped generations and defined eras.',
    accent: '#C4956A',
    imageUrl: 'https://images.unsplash.com/photo-1550399105-c4db5fb85c18?w=1200&h=800&fit=crop&q=80',
    icon: <Library className="w-5 h-5" />,
  },
  {
    title: 'Cybernetic Dreams',
    subtitle: 'Modern Sci-Fi & Tech',
    description: 'Beyond the horizon of reality. Explore galaxies unknown and magic yet discovered through our curated selection of speculative fiction.',
    accent: '#4FD1C5',
    imageUrl: 'https://images.unsplash.com/photo-1543004218-ee141104e323?w=1200&h=800&fit=crop&q=80',
    icon: <Sparkles className="w-5 h-5" />,
  },
  {
    title: 'The Human Canvas',
    subtitle: 'Contemporary Fiction',
    description: 'Diverse voices, modern struggles, and the beauty of the everyday. Discover the stories that map the complex landscape of the human heart.',
    accent: '#818CF8',
    imageUrl: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=1200&h=800&fit=crop&q=80',
    icon: <BookOpen className="w-5 h-5" />,
  },
  {
    title: 'Atlas of Wonder',
    subtitle: 'Global Travel & History',
    description: 'Uncover the secrets of the world without leaving your seat. A collection of narratives that bridge the gap between cultures and continents.',
    accent: '#F6AD55',
    imageUrl: 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=1200&h=800&fit=crop&q=80',
    icon: <Compass className="w-5 h-5" />,
  },
];

export default function ElegantCarousel() {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isTransitioning, setIsTransitioning] = useState(false);
  const [progress, setProgress] = useState(0);
  const [isPaused, setIsPaused] = useState(false);
  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const SLIDE_DURATION = 6000;

  const goToSlide = useCallback((index: number) => {
    if (!Number.isInteger(index) || index < 0 || index >= slides.length || isTransitioning || index === currentIndex) return;
    setIsTransitioning(true);
    setProgress(0);
    setTimeout(() => {
      setCurrentIndex(index);
      setIsTransitioning(false);
    }, 400);
  }, [isTransitioning, currentIndex]);

  const goNext = useCallback(() => {
    goToSlide((currentIndex + 1) % slides.length);
  }, [currentIndex, goToSlide]);

  const goPrev = useCallback(() => {
    goToSlide((currentIndex - 1 + slides.length) % slides.length);
  }, [currentIndex, goToSlide]);

  useEffect(() => {
    if (isPaused) return;
    const progressInterval = setInterval(() => {
      setProgress((prev) => (prev >= 100 ? 100 : prev + 1.2));
    }, 50);

    intervalRef.current = setInterval(goNext, SLIDE_DURATION);

    return () => {
      clearInterval(progressInterval);
      if (intervalRef.current) clearInterval(intervalRef.current);
    };
  }, [currentIndex, isPaused, goNext]);

  const currentSlide = slides.find((_, index) => index === currentIndex) ?? slides[0];

  return (
    <div 
      className="relative w-full h-[700px] overflow-hidden bg-black group"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
    >
      {/* Background with Slow Gradient Shift */}
      <div 
        className="absolute inset-0 transition-opacity duration-1500 opacity-20"
        style={{ 
          background: `radial-gradient(circle at 70% 50%, ${currentSlide.accent} 0%, transparent 70%)` 
        }}
      />

      <div className="relative h-full container mx-auto flex items-center px-12 z-10">
        <div className="w-full lg:w-1/2 space-y-8">
          <div className="flex items-center space-x-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <span className="h-[1px] w-12 bg-white/30" />
            <span className="text-white/60 font-mono text-sm tracking-[0.3em] uppercase flex items-center gap-2">
              {currentSlide.icon}
              {String(currentIndex + 1).padStart(2, '0')} / {String(slides.length).padStart(2, '0')}
            </span>
          </div>

          <h1 className="text-white text-6xl lg:text-8xl font-serif italic border-l-4 pl-8 transition-all duration-700 animate-in fade-in slide-in-from-left-8" style={{ borderColor: currentSlide.accent }}>
            {currentSlide.title}
          </h1>

          <p className="text-lg lg:text-2xl font-light tracking-wide animate-in fade-in slide-in-from-bottom-6 duration-1000 delay-200" style={{ color: currentSlide.accent }}>
            {currentSlide.subtitle}
          </p>

          <p className="max-w-md text-white/50 leading-relaxed text-sm lg:text-base animate-in fade-in slide-in-from-bottom-10 duration-1000 delay-300">
            {currentSlide.description}
          </p>

          <div className="flex space-x-6 pt-6">
            <button onClick={goPrev} className="p-4 rounded-full border border-white/10 hover:bg-white/5 transition-all outline-none">
              <ChevronLeft className="text-white w-6 h-6" />
            </button>
            <button onClick={goNext} className="p-4 rounded-full border border-white/10 hover:bg-white/5 transition-all outline-none">
              <ChevronRight className="text-white w-6 h-6" />
            </button>
            <button className="bg-white text-black px-8 py-4 rounded-full text-xs font-bold uppercase tracking-widest hover:bg-neutral-200 transition-colors shadow-[0_10px_30px_rgba(255,255,255,0.1)]">
              Browse Collection
            </button>
          </div>
        </div>

        <div className="hidden lg:block w-1/2 relative h-[550px] perspective-1000">
          <div className={`w-full h-full relative z-20 transition-all duration-1000 ${isTransitioning ? 'scale-90 opacity-0 -rotate-12 translate-x-20' : 'scale-100 opacity-100 rotate-0 translate-x-0'}`}>
            <img 
              src={currentSlide.imageUrl} 
              alt={currentSlide.title}
              className="w-full h-full object-cover rounded-lg shadow-[0_50px_100px_rgba(0,0,0,0.8)] border border-white/10"
            />
          </div>
          {/* Decorative Elements */}
          <div className="absolute -top-10 -right-10 w-40 h-40 border-t border-r opacity-20 transition-colors duration-1000" style={{ borderColor: currentSlide.accent }} />
          <div className="absolute -bottom-10 -left-10 w-40 h-40 border-b border-l opacity-20 transition-colors duration-1000" style={{ borderColor: currentSlide.accent }} />
        </div>
      </div>

      {/* Modern Pagination Dots */}
      <div className="absolute bottom-12 left-12 flex gap-3">
        {slides.map((_, idx) => (
          <button 
            key={idx}
            onClick={() => goToSlide(idx)}
            className={`h-1 transition-all duration-500 rounded-full ${currentIndex === idx ? 'w-12 bg-white' : 'w-4 bg-white/20'}`}
          />
        ))}
      </div>

      {/* Background Progress Text */}
      <div className="absolute -bottom-20 -right-10 text-[20vw] font-serif font-black text-white/[0.02] select-none pointer-events-none italic">
        {String(currentIndex + 1).padStart(2, '0')}
      </div>
    </div>
  );
}
