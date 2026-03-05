import Link from "next/link";
import Image from "next/image";
import { Github, Twitter, Linkedin, MapPin, Mail } from "lucide-react";

export function Footer() {
    return (
        <footer className="border-t bg-slate-50">
            <div className="container mx-auto py-12 md:py-16">
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
                    {/* Brand */}
                    <div className="col-span-2 lg:col-span-2">
                        <Link href="/" className="flex items-center space-x-2 mb-4">
                            <Image
                                src="/etim-log.png"
                                alt="ETIM Pro"
                                width={160}
                                height={40}
                                className="h-10 w-auto"
                            />
                        </Link>
                        <p className="text-slate-500 text-sm mb-6 max-w-xs">
                            The ultimate product classification extension for WooCommerce. Manage ETIM classes, features, and groups effortlessly.
                        </p>

                        {/* Get in Touch */}
                        <div className="mb-6">
                            <h4 className="font-semibold text-slate-800 mb-3">Get in Touch</h4>
                            <div className="space-y-2.5">
                                <div className="flex items-center gap-2.5 text-sm text-slate-500">
                                    <MapPin className="h-4 w-4 text-blue-500 shrink-0" />
                                    <span>Sockerbruksgatan 753140 Lidk&ouml;ping</span>
                                </div>
                                <div className="flex items-center gap-2.5 text-sm text-slate-500">
                                    <Mail className="h-4 w-4 text-blue-500 shrink-0" />
                                    <a href="mailto:support@virtualtour360.ai" className="hover:text-blue-600 transition-colors">
                                        support@virtualtour360.ai
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center gap-4 text-slate-400">
                            <Link href="#" className="hover:text-blue-600 transition-colors"><Twitter className="h-5 w-5" /></Link>
                            <Link href="#" className="hover:text-blue-600 transition-colors"><Github className="h-5 w-5" /></Link>
                            <Link href="#" className="hover:text-blue-600 transition-colors"><Linkedin className="h-5 w-5" /></Link>
                        </div>
                    </div>

                    {/* Product */}
                    <div>
                        <h4 className="font-semibold text-slate-800 mb-4">Product</h4>
                        <ul className="grid gap-2.5 text-sm text-slate-500">
                            <li><Link href="/features" className="hover:text-blue-600 transition-colors">Features</Link></li>
                            <li><Link href="/pricing" className="hover:text-blue-600 transition-colors">Pricing</Link></li>
                            <li><Link href="/changelog" className="hover:text-blue-600 transition-colors">Changelog</Link></li>
                            <li><Link href="/download" className="hover:text-blue-600 transition-colors">Download</Link></li>
                        </ul>
                    </div>

                    {/* Resources */}
                    <div>
                        <h4 className="font-semibold text-slate-800 mb-4">Resources</h4>
                        <ul className="grid gap-2.5 text-sm text-slate-500">
                            <li><Link href="/docs" className="hover:text-blue-600 transition-colors">Documentation</Link></li>
                            <li><Link href="/docs/api" className="hover:text-blue-600 transition-colors">API Reference</Link></li>
                            <li><Link href="/blog" className="hover:text-blue-600 transition-colors">Blog</Link></li>
                            <li><Link href="/community" className="hover:text-blue-600 transition-colors">Community</Link></li>
                        </ul>
                    </div>

                    {/* Legal */}
                    <div>
                        <h4 className="font-semibold text-slate-800 mb-4">Legal</h4>
                        <ul className="grid gap-2.5 text-sm text-slate-500">
                            <li><Link href="/privacy" className="hover:text-blue-600 transition-colors">Privacy Policy</Link></li>
                            <li><Link href="/terms" className="hover:text-blue-600 transition-colors">Terms of Service</Link></li>
                            <li><Link href="/contact" className="hover:text-blue-600 transition-colors">Contact Us</Link></li>
                        </ul>
                    </div>
                </div>

                <div className="border-t border-slate-200 mt-12 py-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-slate-400">
                    <p>&copy; {new Date().getFullYear()} ETIM Pro. All rights reserved.</p>
                    <p>Built for modern WooCommerce stores.</p>
                </div>
            </div>
        </footer>
    );
}
