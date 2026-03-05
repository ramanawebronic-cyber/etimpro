"use client";

import Link from "next/link";
import { Button } from "@/components/ui/button";
import { motion } from "framer-motion";
import { Menu, X } from "lucide-react";
import { useState } from "react";

export function Navbar() {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <nav className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container flex h-16 items-center justify-between">
        <div className="flex items-center gap-6">
          <Link href="/" className="flex items-center space-x-2">
            <span className="font-bold text-xl inline-block bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
              ETIM Pro
            </span>
          </Link>
          <div className="hidden md:flex gap-6">
            <Link href="/features" className="text-sm font-medium transition-colors hover:text-primary text-muted-foreground">Features</Link>
            <Link href="/pricing" className="text-sm font-medium transition-colors hover:text-primary text-muted-foreground">Pricing</Link>
            <Link href="/docs" className="text-sm font-medium transition-colors hover:text-primary text-muted-foreground">Docs</Link>
            <Link href="/changelog" className="text-sm font-medium transition-colors hover:text-primary text-muted-foreground">Changelog</Link>
          </div>
        </div>
        <div className="hidden md:flex items-center gap-4">
          <Link href="/contact" className="text-sm font-medium text-muted-foreground hover:text-primary">Contact Sales</Link>
          <Button asChild>
            <Link href="/download">Get Plugin</Link>
          </Button>
        </div>
        
        <button className="md:hidden" onClick={() => setIsOpen(!isOpen)}>
          {isOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
        </button>
      </div>
      
      {/* Mobile Menu */}
      {isOpen && (
        <motion.div 
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          className="md:hidden border-b p-4 grid gap-4 bg-background"
        >
          <Link href="/features" onClick={() => setIsOpen(false)} className="text-sm font-medium">Features</Link>
          <Link href="/pricing" onClick={() => setIsOpen(false)} className="text-sm font-medium">Pricing</Link>
          <Link href="/docs" onClick={() => setIsOpen(false)} className="text-sm font-medium">Documentation</Link>
          <Link href="/changelog" onClick={() => setIsOpen(false)} className="text-sm font-medium">Changelog</Link>
          <Link href="/contact" onClick={() => setIsOpen(false)} className="text-sm font-medium">Contact</Link>
          <Button asChild className="w-full mt-2">
            <Link href="/download">Get Plugin</Link>
          </Button>
        </motion.div>
      )}
    </nav>
  );
}
