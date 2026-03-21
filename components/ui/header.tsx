"use client";

import { Button } from "@/components/ui/button";
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
} from "@/components/ui/navigation-menu";
import { Menu, MoveRight, X, BookOpen } from "lucide-react";
import { useState } from "react";

function Header1() {
    const navigationItems = [
        {
            title: "Home",
            href: "/",
            description: "",
        },
        {
            title: "Collections",
            description: "Explore our curated book collections across all genres.",
            items: [
                {
                    title: "Best Sellers",
                    href: "/categories/best-sellers",
                },
                {
                    title: "New Arrivals",
                    href: "/categories/new-arrivals",
                },
                {
                    title: "Rare Classics",
                    href: "/categories/classics",
                },
                {
                    title: "Sci-Fi & Tech",
                    href: "/categories/scifi",
                },
            ],
        },
        {
            title: "Services",
            description: "We offer more than just books.",
            items: [
                {
                    title: "Book Club",
                    href: "/services/book-club",
                },
                {
                    title: "Author Events",
                    href: "/services/events",
                },
                {
                    title: "Gift Cards",
                    href: "/services/gifts",
                },
                {
                    title: "Study Spaces",
                    href: "/services/spaces",
                },
            ],
        },
    ];

    const [isOpen, setOpen] = useState(false);
    return (
        <header className="w-full z-50 fixed top-0 left-0 bg-neutral-950/80 backdrop-blur-md border-b border-white/5">
            <div className="container relative mx-auto min-h-20 flex gap-4 flex-row lg:grid lg:grid-cols-3 items-center px-4 lg:px-12">
                <div className="justify-start items-center gap-4 lg:flex hidden flex-row">
                    <NavigationMenu className="flex justify-start items-start">
                        <NavigationMenuList className="flex justify-start gap-4 flex-row">
                            {navigationItems.map((item) => (
                                <NavigationMenuItem key={item.title}>
                                    {item.href ? (
                                        <NavigationMenuLink href={item.href}>
                                            <Button variant="ghost" className="text-white/70 hover:text-white">{item.title}</Button>
                                        </NavigationMenuLink>
                                    ) : (
                                        <>
                                            <NavigationMenuTrigger className="font-medium text-sm text-white/70 hover:text-white bg-transparent">
                                                {item.title}
                                            </NavigationMenuTrigger>
                                            <NavigationMenuContent className="!w-[450px] p-4 bg-neutral-900 border border-white/10 rounded-xl shadow-2xl">
                                                <div className="flex flex-col lg:grid grid-cols-2 gap-4">
                                                    <div className="flex flex-col h-full justify-between">
                                                        <div className="flex flex-col gap-2">
                                                            <p className="text-base font-bold text-white">{item.title}</p>
                                                            <p className="text-white/40 text-sm leading-relaxed">
                                                                {item.description}
                                                            </p>
                                                        </div>
                                                        <Button size="sm" className="mt-10 bg-white text-black hover:bg-neutral-200">
                                                            Explore Now
                                                        </Button>
                                                    </div>
                                                    <div className="flex flex-col text-sm h-full justify-end">
                                                        {item.items?.map((subItem) => (
                                                            <NavigationMenuLink
                                                                href={subItem.href}
                                                                key={subItem.title}
                                                                className="flex flex-row justify-between items-center hover:bg-white/5 py-2 px-4 rounded-lg transition-colors group"
                                                            >
                                                                <span className="text-white/60 group-hover:text-white">{subItem.title}</span>
                                                                <MoveRight className="w-4 h-4 text-white/20 group-hover:text-white/60" />
                                                            </NavigationMenuLink>
                                                        ))}
                                                    </div>
                                                </div>
                                            </NavigationMenuContent>
                                        </>
                                    )}
                                </NavigationMenuItem>
                            ))}
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>
                <div className="flex lg:justify-center items-center gap-2">
                    <BookOpen className="w-6 h-6 text-white" />
                    <p className="font-serif italic text-2xl font-bold tracking-tight text-white">Bookstore</p>
                </div>
                <div className="flex justify-end w-full gap-4 items-center">
                    <Button variant="ghost" className="hidden md:inline text-white/50 hover:text-white hover:bg-white/5">
                        My Account
                    </Button>
                    <div className="h-4 w-[1px] bg-white/10 hidden md:inline"></div>
                    <Button variant="outline" className="border-white/10 text-white hover:bg-white/5">Sign in</Button>
                    <Button className="bg-white text-black hover:bg-neutral-200">Get started</Button>
                </div>
                <div className="flex w-12 shrink lg:hidden items-end justify-end">
                    <Button variant="ghost" onClick={() => setOpen(!isOpen)} className="text-white">
                        {isOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
                    </Button>
                    {isOpen && (
                        <div className="absolute top-20 border-t border-white/5 flex flex-col w-full right-0 bg-neutral-950 shadow-2xl py-8 container px-6 gap-8 animate-in fade-in slide-in-from-top-4">
                            {navigationItems.map((item) => (
                                <div key={item.title}>
                                    <div className="flex flex-col gap-2">
                                        {item.href ? (
                                            <a
                                                href={item.href}
                                                className="flex justify-between items-center text-white/80"
                                            >
                                                <span className="text-xl font-medium">{item.title}</span>
                                                <MoveRight className="w-4 h-4 stroke-1" />
                                            </a>
                                        ) : (
                                            <p className="text-xl font-bold text-white mb-2">{item.title}</p>
                                        )}
                                        <div className="grid grid-cols-2 gap-x-4 gap-y-2">
                                            {item.items &&
                                                item.items.map((subItem) => (
                                                    <a
                                                        key={subItem.title}
                                                        href={subItem.href}
                                                        className="flex justify-between items-center text-white/40 hover:text-white transition-colors"
                                                    >
                                                        <span className="text-sm">
                                                            {subItem.title}
                                                        </span>
                                                        <MoveRight className="w-3 h-3 opacity-0 hover:opacity-100" />
                                                    </a>
                                                ))}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}

export { Header1 };
