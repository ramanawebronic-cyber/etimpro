"use client";

import Link from "next/link";
import { Button } from "@/components/ui/button";
import { motion } from "framer-motion";
import { Menu, X } from "lucide-react";
import { useState } from "react";

export function Navbar() {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <nav className="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
      <div className="container mx-auto flex h-16 items-center">
        {/* Logo - Left */}
        <div className="flex items-center">
          <Link href="/" className="flex items-center space-x-2">
            <span className="font-bold text-xl inline-block bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">
              ETIM Pro
            </span>
          </Link>
        </div>

        {/* Nav Links - Center */}
        <div className="hidden md:flex flex-1 justify-center gap-8">
          <Link href="/features" className="text-sm font-medium transition-colors hover:text-blue-600 text-slate-600">Features</Link>
          <Link href="/pricing" className="text-sm font-medium transition-colors hover:text-blue-600 text-slate-600">Pricing</Link>
          <Link href="/docs" className="text-sm font-medium transition-colors hover:text-blue-600 text-slate-600">Docs</Link>
          <Link href="/changelog" className="text-sm font-medium transition-colors hover:text-blue-600 text-slate-600">Changelog</Link>
          <Link href="/contact" className="text-sm font-medium transition-colors hover:text-blue-600 text-slate-600">Contact</Link>
        </div>

        {/* CTA - Right */}
        <div className="hidden md:flex items-center gap-4">
          <Button asChild className="bg-blue-600 hover:bg-blue-700 text-white rounded-full px-6">
            <Link href="/download">Get Plugin</Link>
          </Button>
        </div>

        <button className="md:hidden ml-auto" onClick={() => setIsOpen(!isOpen)}>
          {isOpen ? <X className="h-6 w-6 text-slate-700" /> : <Menu className="h-6 w-6 text-slate-700" />}
        </button>
      </div>

      {/* Mobile Menu */}
      {isOpen && (
        <motion.div
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          className="md:hidden border-b p-4 grid gap-4 bg-white"
        >
          <Link href="/features" onClick={() => setIsOpen(false)} className="text-sm font-medium text-slate-700 hover:text-blue-600">Features</Link>
          <Link href="/pricing" onClick={() => setIsOpen(false)} className="text-sm font-medium text-slate-700 hover:text-blue-600">Pricing</Link>
          <Link href="/docs" onClick={() => setIsOpen(false)} className="text-sm font-medium text-slate-700 hover:text-blue-600">Documentation</Link>
          <Link href="/changelog" onClick={() => setIsOpen(false)} className="text-sm font-medium text-slate-700 hover:text-blue-600">Changelog</Link>
          <Link href="/contact" onClick={() => setIsOpen(false)} className="text-sm font-medium text-slate-700 hover:text-blue-600">Contact</Link>
          <Button asChild className="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white rounded-full">
            <Link href="/download">Get Plugin</Link>
          </Button>
        </motion.div>
      )}
    </nav>
  );
}
