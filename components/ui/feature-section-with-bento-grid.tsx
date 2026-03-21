import React from "react";
import {
  BookOpen,
  Globe,
  ShieldCheck,
  Award,
  Sparkles,
  Library,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

/* ─── Types ─────────────────────────────────────────────── */
interface BentoCardProps {
  icon: React.ReactNode;
  title: string;
  description: string;
  accent?: string;
  label?: string;
  wide?: boolean;
  imageUrl?: string;
  className?: string;
}

/* ─── Bento Card ─────────────────────────────────────────── */
function BentoCard({
  icon,
  title,
  description,
  accent = "rgba(255,255,255,0.06)",
  label,
  imageUrl,
  className,
}: BentoCardProps) {
  return (
    <div
      className={cn(
        "group relative rounded-2xl overflow-hidden border border-white/5 transition-all duration-500",
        "hover:border-white/10 hover:-translate-y-1 hover:shadow-[0_24px_60px_rgba(0,0,0,0.6)]",
        className,
      )}
      style={{
        background:
          "linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%)",
        backdropFilter: "blur(12px)",
      }}
    >
      {/* Subtle radial glow on hover */}
      <div
        className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"
        style={{
          background: `radial-gradient(circle at 30% 80%, ${accent} 0%, transparent 65%)`,
        }}
      />

      {/* Background image if provided */}
      {imageUrl && (
        <div className="absolute inset-0 z-0">
          <img
            src={imageUrl}
            alt=""
            className="w-full h-full object-cover opacity-10 group-hover:opacity-15 transition-opacity duration-700 scale-105 group-hover:scale-100 transition-transform"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-black/40" />
        </div>
      )}

      {/* Content */}
      <div className="relative z-10 h-full flex flex-col justify-between p-7">
        {/* Top row */}
        <div className="flex items-start justify-between">
          <div className="p-2.5 rounded-xl bg-white/5 border border-white/5 group-hover:bg-white/8 transition-colors">
            <div className="text-white/50 group-hover:text-white/80 transition-colors">
              {icon}
            </div>
          </div>
          {label && (
            <span
              className="font-mono text-[9px] tracking-[0.25em] text-white/20 uppercase border border-white/8 px-2 py-1 rounded-full"
            >
              {label}
            </span>
          )}
        </div>

        {/* Bottom text */}
        <div className="mt-8 space-y-2">
          {/* Decorative line */}
          <div className="w-8 h-px bg-white/15 mb-4 group-hover:w-12 transition-all duration-500" />
          <h3 className="text-white font-serif text-xl leading-snug tracking-tight">
            {title}
          </h3>
          <p className="text-white/40 text-sm leading-relaxed font-light">
            {description}
          </p>
        </div>
      </div>
    </div>
  );
}

/* ─── Main Feature Section ───────────────────────────────── */
function CollectionFeature() {
  const cards: BentoCardProps[] = [
    {
      icon: <Library className="w-5 h-5" />,
      title: "Rare & First Editions",
      description:
        "Meticulously sourced first editions and rare prints — items that belong in the hands of true collectors.",
      accent: "rgba(196,149,106,0.25)",
      label: "CURATED",
      imageUrl:
        "https://images.unsplash.com/photo-1550399105-c4db5fb85c18?w=800&h=600&fit=crop&q=80",
      wide: true,
    },
    {
      icon: <Globe className="w-5 h-5" />,
      title: "Global Sourcing",
      description:
        "Our network spans continents — from Tokyo to Paris — to bring you works that transcend borders.",
      accent: "rgba(79,209,197,0.2)",
      label: "WORLDWIDE",
      imageUrl:
        "https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=600&h=600&fit=crop&q=80",
    },
    {
      icon: <ShieldCheck className="w-5 h-5" />,
      title: "Authenticated & Insured",
      description:
        "Every volume is authenticated and delivered with full insurance. We stand behind every order.",
      accent: "rgba(129,140,248,0.2)",
      label: "VERIFIED",
      imageUrl:
        "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=600&h=600&fit=crop&q=80",
    },
    {
      icon: <Award className="w-5 h-5" />,
      title: "Expert Recommendations",
      description:
        "Our editorial team handpicks each selection with the discernment of a lifetime in letters.",
      accent: "rgba(246,173,85,0.2)",
      label: "EDITORIAL",
      imageUrl:
        "https://images.unsplash.com/photo-1519682337058-a94d519337bc?w=600&h=600&fit=crop&q=80",
      wide: true,
    },
    {
      icon: <Sparkles className="w-5 h-5" />,
      title: "Limited Editions",
      description:
        "Numbered prints and signed copies — works of art as much as literature.",
      accent: "rgba(196,149,106,0.2)",
      label: "EXCLUSIVE",
      imageUrl:
        "https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=600&h=600&fit=crop&q=80",
    },
    {
      icon: <BookOpen className="w-5 h-5" />,
      title: "Timeless Classics",
      description:
        "The canon, preserved and presented with the reverence it deserves. Philosophy, literature, history.",
      accent: "rgba(255,255,255,0.1)",
      label: "HERITAGE",
      imageUrl:
        "https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=600&h=600&fit=crop&q=80",
    },
  ];

  return (
    <section className="w-full py-24 lg:py-36" style={{ background: "#000" }}>
      <div className="max-w-7xl mx-auto px-6 lg:px-12">
        {/* Section header */}
        <div className="flex flex-col gap-10 mb-16">
          <div className="flex flex-col gap-4 items-start">
            {/* Eyebrow */}
            <div className="flex items-center gap-4">
              <div className="h-px w-8 bg-white/20" />
              <Badge
                className="font-mono text-[9px] tracking-[0.25em] uppercase bg-white/5 text-white/40 border-white/10 rounded-full hover:bg-white/8"
                variant="outline"
              >
                The Collection
              </Badge>
            </div>

            {/* Title block */}
            <div className="flex flex-col gap-3">
              <h2 className="text-4xl md:text-6xl font-serif text-white leading-[1.05] tracking-tight">
                L'Atelier{" "}
                <span
                  style={{
                    background: "linear-gradient(to right, #fff, #666)",
                    WebkitBackgroundClip: "text",
                    WebkitTextFillColor: "transparent",
                  }}
                  className="italic"
                >
                  Collection
                </span>
              </h2>
              <p
                className="text-sm max-w-lg leading-relaxed tracking-wide font-mono"
                style={{ color: "rgba(255,255,255,0.35)" }}
              >
                From timeless classics to modern-day masterpieces — explore our
                curated selection of fine books selected by those who live and
                breathe the written word.
              </p>
            </div>
          </div>

          {/* Stats row */}
          <div className="flex flex-wrap gap-6">
            {[
              { value: "2,400+", label: "Titles" },
              { value: "140+", label: "Countries Sourced" },
              { value: "100%", label: "Authenticated" },
            ].map((stat) => (
              <div key={stat.label} className="flex items-baseline gap-2">
                <span className="text-white font-serif text-2xl">
                  {stat.value}
                </span>
                <span className="font-mono text-[10px] tracking-[0.2em] text-white/25 uppercase">
                  {stat.label}
                </span>
              </div>
            ))}
          </div>
        </div>

        {/* Bento Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 auto-rows-[260px]">
          {/* Card 1 — wide */}
          <BentoCard
            {...cards[0]}
            className="sm:col-span-2 lg:col-span-2"
          />
          {/* Card 2 */}
          <BentoCard {...cards[1]} />
          {/* Card 3 */}
          <BentoCard {...cards[2]} />
          {/* Card 4 — wide */}
          <BentoCard
            {...cards[3]}
            className="sm:col-span-2 lg:col-span-2"
          />
          {/* Card 5 */}
          <BentoCard {...cards[4]} />
          {/* Card 6 */}
          <BentoCard
            {...cards[5]}
            className="sm:col-span-2 lg:col-span-2"
          />
        </div>

        {/* CTA */}
        <div className="mt-12 flex items-center gap-8">
          <a
            href="categories/index.php"
            className="inline-flex items-center gap-3 text-xs font-mono tracking-[0.2em] text-white/50 uppercase hover:text-white transition-colors group"
          >
            <span
              className="h-px w-8 bg-white/20 group-hover:w-12 group-hover:bg-white/50 transition-all duration-300"
            />
            Explore All Titles
            <svg
              className="w-3 h-3 group-hover:translate-x-1 transition-transform"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M9 5l7 7-7 7"
              />
            </svg>
          </a>
          <div className="h-px flex-1 bg-white/5" />
          <span className="font-mono text-[9px] text-white/15 tracking-[0.3em] uppercase">
            VITRUVIAN · EST. MMXXV
          </span>
        </div>
      </div>
    </section>
  );
}

export { CollectionFeature };
