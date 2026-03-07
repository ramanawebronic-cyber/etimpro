"use client";

import Link from "next/link";
import Image from "next/image";
import etimLog from "../../public/etim-log.png";
import { Button } from "@/components/ui/button";
import { motion } from "framer-motion";
import { Menu, X, User } from "lucide-react";
import { useState, useEffect } from "react";
import { usePathname } from "next/navigation";

export function Navbar() {
  const [isOpen, setIsOpen] = useState(false);
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const pathname = usePathname();

  useEffect(() => {
    // Check if user is logged in
    const token = localStorage.getItem("etim_token");
    setIsLoggedIn(!!token);
  }, [pathname]);

  const navLinks = [
    { name: "Home", href: "/" },
    { name: "Features", href: "/features" },
    { name: "Pricing", href: "/pricing" },
    { name: "Docs", href: "/docs" },
    { name: "Changelog", href: "/changelog" },
    { name: "Contact", href: "/contact" },
  ];

  return (
    <nav className="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
      <div className="container mx-auto flex h-16 items-center px-4 md:px-6">
        {/* Logo - Left */}
        <div className="flex items-center">
          <Link href="/" className="flex items-center space-x-2">
            <Image
              src={etimLog}
              alt="ETIM Pro"
              width={160}
              height={40}
              className="h-10 w-auto"
            />
          </Link>
        </div>

        {/* Nav Links - Center */}
        <div className="hidden md:flex flex-1 justify-center gap-6">
          {navLinks.map((link) => (
            <Link
              key={link.name}
              href={link.href}
              className={`text-sm font-medium transition-all px-2 py-1 hover:text-blue-600 ${pathname === link.href ? "text-blue-600" : "text-slate-600"}`}
            >
              {link.name}
            </Link>
          ))}
        </div>

        {/* CTA - Right */}
        <div className="hidden md:flex items-center gap-3">
          <Link
            href={isLoggedIn ? "/my-account" : "/login"}
            className={`text-sm font-bold px-5 py-2 rounded-full transition-all border ${isLoggedIn
                ? "bg-slate-900 text-white border-slate-900 hover:bg-slate-800"
                : "bg-blue-50 text-blue-600 border-blue-200 hover:bg-blue-600 hover:text-white"
              }`}
          >
            {isLoggedIn ? (
              <span className="flex items-center gap-2">
                <User className="h-4 w-4" /> My Account
              </span>
            ) : "Login"}
          </Link>

          <Button asChild className="bg-blue-600 hover:bg-blue-700 text-white rounded-full px-6 shadow-lg shadow-blue-600/10">
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
          {navLinks.map((link) => (
            <Link
              key={link.name}
              href={link.href}
              onClick={() => setIsOpen(false)}
              className={`text-sm font-medium transition-all p-2 hover:text-blue-600 ${pathname === link.href ? "text-blue-600" : "text-slate-700"}`}
            >
              {link.name}
            </Link>
          ))}

          <div className="grid gap-2 mt-2">
            <Button asChild variant="outline" className="w-full rounded-xl border-slate-200">
              <Link href={isLoggedIn ? "/my-account" : "/login"} onClick={() => setIsOpen(false)}>
                {isLoggedIn ? "My Account" : "Login"}
              </Link>
            </Button>
            <Button asChild className="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl">
              <Link href="/download" onClick={() => setIsOpen(false)}>Get Plugin</Link>
            </Button>
          </div>
        </motion.div>
      )}
    </nav>
  );
}
