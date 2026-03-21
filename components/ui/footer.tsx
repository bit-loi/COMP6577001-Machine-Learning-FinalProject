"use client";

import { BookOpen, Github, Instagram, Twitter, MoveRight } from "lucide-react";
import { Button } from "@/components/ui/button";

function Footer() {
    const footerLinks = [
        {
            title: "Navigation",
            links: [
                { name: "Home", href: "/" },
                { name: "Categories", href: "/categories" },
                { name: "Best Sellers", href: "/best-sellers" },
                { name: "New Arrivals", href: "/new-arrivals" },
            ],
        },
        {
            title: "Support",
            links: [
                { name: "Contact Us", href: "/contact" },
                { name: "Shipping Info", href: "/shipping" },
                { name: "Returns", href: "/returns" },
                { name: "FAQ", href: "/faq" },
            ],
        },
        {
            title: "Legal",
            links: [
                { name: "Privacy Policy", href: "/privacy" },
                { name: "Terms of Service", href: "/terms" },
                { name: "Cookie Policy", href: "/cookies" },
            ],
        },
    ];

    return (
        <footer className="w-full bg-neutral-950 border-t border-white/5 pt-20 pb-12 mt-20">
            <div className="container mx-auto px-4 lg:px-12">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 lg:gap-8">
                    <div className="lg:col-span-2 space-y-6">
                        <div className="flex items-center gap-2">
                            <BookOpen className="w-6 h-6 text-white" />
                            <p className="font-serif italic text-2xl font-bold tracking-tight text-white">Bookstore</p>
                        </div>
                        <p className="text-white/40 max-w-sm leading-relaxed">
                            Curating the finest literature from across the globe. Our mission is to connect readers with stories that inspire, challenge, and endure.
                        </p>
                        <div className="flex items-center gap-4">
                            <Button variant="ghost" size="icon" className="rounded-full text-white/40 hover:text-white hover:bg-white/5">
                                <Twitter className="w-4 h-4" />
                            </Button>
                            <Button variant="ghost" size="icon" className="rounded-full text-white/40 hover:text-white hover:bg-white/5">
                                <Instagram className="w-4 h-4" />
                            </Button>
                            <Button variant="ghost" size="icon" className="rounded-full text-white/40 hover:text-white hover:bg-white/5">
                                <Github className="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    {footerLinks.map((section) => (
                        <div key={section.title} className="space-y-6">
                            <h4 className="text-sm font-bold uppercase tracking-widest text-white">{section.title}</h4>
                            <ul className="space-y-4">
                                {section.links.map((link) => (
                                    <li key={link.name}>
                                        <a href={link.href} className="text-white/40 hover:text-white transition-colors text-sm">
                                            {link.name}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>

                <div className="mt-20 pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
                    <p className="text-white/20 text-xs">
                        © {new Date().getFullYear()} Bookstore Inc. All rights reserved.
                    </p>
                    <div className="flex items-center gap-8">
                        <a href="#" className="text-white/20 hover:text-white transition-colors text-xs">Privacy</a>
                        <a href="#" className="text-white/20 hover:text-white transition-colors text-xs">Terms</a>
                        <a href="#" className="text-white/20 hover:text-white transition-colors text-xs">Cookies</a>
                    </div>
                </div>
            </div>
            
            {/* Background Accent */}
            <div className="absolute bottom-0 right-0 w-[500px] h-[500px] bg-white/[0.01] blur-[120px] rounded-full pointer-events-none -z-10" />
        </footer>
    );
}

export { Footer };
