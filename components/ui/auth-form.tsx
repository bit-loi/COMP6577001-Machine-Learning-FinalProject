"use client";
import React from "react";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import {
  IconBrandGithub,
  IconBrandGoogle,
} from "@tabler/icons-react";
import { BookOpen } from "lucide-react";

// ---- Sub-components ----

const BottomGradient = () => (
  <>
    <span className="group-hover/btn:opacity-100 block transition duration-500 opacity-0 absolute h-px w-full -bottom-px inset-x-0 bg-gradient-to-r from-transparent via-white/40 to-transparent" />
    <span className="group-hover/btn:opacity-100 blur-sm block transition duration-500 opacity-0 absolute h-px w-1/2 mx-auto -bottom-px inset-x-10 bg-gradient-to-r from-transparent via-white/20 to-transparent" />
  </>
);

const LabelInputContainer = ({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) => (
  <div className={cn("flex flex-col space-y-2 w-full", className)}>
    {children}
  </div>
);

// ---- Login Form ----

export function LoginForm({
  actionUrl,
  errorMessage,
}: {
  actionUrl: string;
  errorMessage?: string;
}) {
  return (
    <div className="w-full max-w-md mx-auto">
      {/* Logo */}
      <div className="flex items-center gap-2 mb-10">
        <BookOpen className="w-6 h-6 text-white" />
        <span className="font-serif italic text-xl font-bold text-white">Bookstore</span>
      </div>

      <h2 className="text-3xl font-bold text-white mb-2">Welcome back</h2>
      <p className="text-white/40 text-sm mb-10">
        Sign in to your account to continue your reading journey.
      </p>

      {errorMessage && (
        <div className="mb-6 px-4 py-3 rounded-lg text-sm text-red-400" style={{ background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.2)" }}>
          {errorMessage}
        </div>
      )}

      <form method="POST" action={actionUrl} className="space-y-5">
        <LabelInputContainer>
          <Label htmlFor="email">Email address</Label>
          <Input id="email" name="email" placeholder="you@example.com" type="email" required />
        </LabelInputContainer>

        <LabelInputContainer>
          <div className="flex justify-between items-center">
            <Label htmlFor="password">Password</Label>
            <a href="#" className="text-xs text-white/30 hover:text-white transition-colors">Forgot password?</a>
          </div>
          <Input id="password" name="password" placeholder="••••••••" type="password" required />
        </LabelInputContainer>

        <button
          name="login"
          type="submit"
          className="relative group/btn w-full h-11 rounded-lg font-semibold text-sm transition-all duration-200 mt-2"
          style={{ background: "white", color: "#050505" }}
          onMouseOver={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "#e5e5e5"; }}
          onMouseOut={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "white"; }}
        >
          Sign in →
          <BottomGradient />
        </button>

        {/* Divider */}
        <div className="flex items-center gap-4 my-6">
          <div className="flex-1 h-px" style={{ background: "rgba(255,255,255,0.06)" }}></div>
          <span className="text-xs text-white/20 uppercase tracking-widest">or</span>
          <div className="flex-1 h-px" style={{ background: "rgba(255,255,255,0.06)" }}></div>
        </div>

        {/* OAuth Buttons */}
        <div className="space-y-3">
          <button
            type="button"
            className="relative group/btn flex items-center justify-center gap-3 w-full h-11 rounded-lg text-sm font-medium transition-all duration-200"
            style={{ background: "rgba(255,255,255,0.04)", border: "1px solid rgba(255,255,255,0.08)", color: "rgba(255,255,255,0.7)" }}
            onMouseOver={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "rgba(255,255,255,0.08)"; }}
            onMouseOut={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "rgba(255,255,255,0.04)"; }}
          >
            <IconBrandGoogle className="h-4 w-4" />
            Continue with Google
            <BottomGradient />
          </button>
          <button
            type="button"
            className="relative group/btn flex items-center justify-center gap-3 w-full h-11 rounded-lg text-sm font-medium transition-all duration-200"
            style={{ background: "rgba(255,255,255,0.04)", border: "1px solid rgba(255,255,255,0.08)", color: "rgba(255,255,255,0.7)" }}
            onMouseOver={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "rgba(255,255,255,0.08)"; }}
            onMouseOut={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "rgba(255,255,255,0.04)"; }}
          >
            <IconBrandGithub className="h-4 w-4" />
            Continue with GitHub
            <BottomGradient />
          </button>
        </div>

        <p className="text-center text-sm text-white/30 mt-8">
          Don't have an account?{" "}
          <a href="register.php" className="text-white/70 hover:text-white transition-colors font-medium">
            Create one
          </a>
        </p>
      </form>
    </div>
  );
}

// ---- Register Form ----

export function RegisterForm({
  actionUrl,
  errorMessage,
}: {
  actionUrl: string;
  errorMessage?: string;
}) {
  return (
    <div className="w-full max-w-md mx-auto">
      {/* Logo */}
      <div className="flex items-center gap-2 mb-10">
        <BookOpen className="w-6 h-6 text-white" />
        <span className="font-serif italic text-xl font-bold text-white">Bookstore</span>
      </div>

      <h2 className="text-3xl font-bold text-white mb-2">Create account</h2>
      <p className="text-white/40 text-sm mb-10">
        Join thousands of readers discovering their next great book.
      </p>

      {errorMessage && (
        <div className="mb-6 px-4 py-3 rounded-lg text-sm text-red-400" style={{ background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.2)" }}>
          {errorMessage}
        </div>
      )}

      <form method="POST" action={actionUrl} className="space-y-5">
        <LabelInputContainer>
          <Label htmlFor="username">Username</Label>
          <Input id="username" name="username" placeholder="johndoe" type="text" required />
        </LabelInputContainer>

        <LabelInputContainer>
          <Label htmlFor="email">Email address</Label>
          <Input id="email" name="email" placeholder="you@example.com" type="email" required />
        </LabelInputContainer>

        <LabelInputContainer>
          <Label htmlFor="password">Password</Label>
          <Input id="password" name="password" placeholder="Min. 8 characters" type="password" required />
        </LabelInputContainer>

        <button
          name="register"
          type="submit"
          className="relative group/btn w-full h-11 rounded-lg font-semibold text-sm transition-all duration-200 mt-2"
          style={{ background: "white", color: "#050505" }}
          onMouseOver={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "#e5e5e5"; }}
          onMouseOut={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "white"; }}
        >
          Create account →
          <BottomGradient />
        </button>

        {/* Divider */}
        <div className="flex items-center gap-4 my-6">
          <div className="flex-1 h-px" style={{ background: "rgba(255,255,255,0.06)" }}></div>
          <span className="text-xs text-white/20 uppercase tracking-widest">or</span>
          <div className="flex-1 h-px" style={{ background: "rgba(255,255,255,0.06)" }}></div>
        </div>

        {/* OAuth Buttons */}
        <div className="space-y-3">
          <button
            type="button"
            className="relative group/btn flex items-center justify-center gap-3 w-full h-11 rounded-lg text-sm font-medium transition-all duration-200"
            style={{ background: "rgba(255,255,255,0.04)", border: "1px solid rgba(255,255,255,0.08)", color: "rgba(255,255,255,0.7)" }}
            onMouseOver={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "rgba(255,255,255,0.08)"; }}
            onMouseOut={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "rgba(255,255,255,0.04)"; }}
          >
            <IconBrandGoogle className="h-4 w-4" />
            Sign up with Google
            <BottomGradient />
          </button>
          <button
            type="button"
            className="relative group/btn flex items-center justify-center gap-3 w-full h-11 rounded-lg text-sm font-medium transition-all duration-200"
            style={{ background: "rgba(255,255,255,0.04)", border: "1px solid rgba(255,255,255,0.08)", color: "rgba(255,255,255,0.7)" }}
            onMouseOver={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "rgba(255,255,255,0.08)"; }}
            onMouseOut={(e) => { (e.currentTarget as HTMLButtonElement).style.background = "rgba(255,255,255,0.04)"; }}
          >
            <IconBrandGithub className="h-4 w-4" />
            Sign up with GitHub
            <BottomGradient />
          </button>
        </div>

        <p className="text-center text-sm text-white/30 mt-8">
          Already have an account?{" "}
          <a href="login.php" className="text-white/70 hover:text-white transition-colors font-medium">
            Sign in
          </a>
        </p>
      </form>
    </div>
  );
}
