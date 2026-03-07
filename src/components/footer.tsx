import Link from "next/link";
import Image from "next/image";
import etimLog from "../../public/etim-log.png";
import { Facebook, Youtube, Instagram, Linkedin, MapPin, Mail } from "lucide-react";

export function Footer() {
    return (
        <footer className="border-t bg-slate-50">
            <div className="container mx-auto py-12 md:py-16 px-4 md:px-6">
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-12">
                    {/* Brand */}
                    <div className="lg:col-span-1">
                        <Link href="/" className="flex items-center space-x-2 mb-6">
                            <Image
                                src={etimLog}
                                alt="ETIM Pro"
                                width={160}
                                height={40}
                                className="h-10 w-auto"
                            />
                        </Link>
                        <p className="text-slate-500 text-sm leading-relaxed mb-6">
                            The ultimate product classification extension for WooCommerce. Manage ETIM classes, features, and groups effortlessly.
                        </p>
                        <div className="flex items-center gap-3">
                            <SocialLink href="https://www.facebook.com/thingsatweb" icon={<Facebook className="h-5 w-5" />} />
                            <SocialLink href="https://www.youtube.com/@thingsatweb" icon={<Youtube className="h-5 w-5" />} />
                            <SocialLink href="https://www.instagram.com/thingsatweb/" icon={<Instagram className="h-5 w-5" />} />
                            <SocialLink href="https://www.linkedin.com/company/thingsatweb/" icon={<Linkedin className="h-5 w-5" />} />
                        </div>
                    </div>

                    {/* Product */}
                    <div>
                        <h4 className="font-bold text-slate-900 mb-6 uppercase tracking-wider text-xs">Product</h4>
                        <ul className="space-y-4 text-sm text-slate-500">
                            <li><Link href="/features" className="hover:text-blue-600 transition-colors">Features</Link></li>
                            <li><Link href="/pricing" className="hover:text-blue-600 transition-colors">Pricing</Link></li>
                            <li><Link href="/changelog" className="hover:text-blue-600 transition-colors">Changelog</Link></li>
                            <li><Link href="/download" className="hover:text-blue-600 transition-colors">Download</Link></li>
                        </ul>
                    </div>

                    {/* Resources */}
                    <div>
                        <h4 className="font-bold text-slate-900 mb-6 uppercase tracking-wider text-xs">Resources</h4>
                        <ul className="space-y-4 text-sm text-slate-500">
                            <li><Link href="/docs" className="hover:text-blue-600 transition-colors">Documentation</Link></li>
                            <li><Link href="/docs/api" className="hover:text-blue-600 transition-colors">API Reference</Link></li>
                            <li><Link href="/blog" className="hover:text-blue-600 transition-colors">Blog</Link></li>
                            <li><Link href="/community" className="hover:text-blue-600 transition-colors">Community</Link></li>
                        </ul>
                    </div>

                    {/* Legal */}
                    <div>
                        <h4 className="font-bold text-slate-900 mb-6 uppercase tracking-wider text-xs">Legal</h4>
                        <ul className="space-y-4 text-sm text-slate-500">
                            <li><Link href="/privacy" className="hover:text-blue-600 transition-colors">Privacy Policy</Link></li>
                            <li><Link href="/terms" className="hover:text-blue-600 transition-colors">Terms of Service</Link></li>
                            <li><Link href="/contact" className="hover:text-blue-600 transition-colors">Contact Us</Link></li>
                        </ul>
                    </div>

                    {/* Get in Touch */}
                    <div>
                        <h4 className="font-bold text-slate-900 mb-6 uppercase tracking-wider text-xs">Get in Touch</h4>
                        <div className="space-y-4">
                            <div className="flex items-start gap-3 text-sm text-slate-500">
                                <MapPin className="h-5 w-5 text-blue-500 shrink-0" />
                                <span>Sockerbruksgatan 7<br />53140 Lidk&ouml;ping</span>
                            </div>
                            <div className="flex items-center gap-3 text-sm text-slate-500">
                                <Mail className="h-5 w-5 text-blue-500 shrink-0" />
                                <a href="mailto:support@etimpro.ai" className="hover:text-blue-600 transition-all font-medium">
                                    support@etimpro.ai
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="border-t border-slate-200 mt-16 py-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-slate-400">
                    <p>&copy; 2026 Things at Web Sweden AB. All Rights Reserved.</p>
                    <p className="flex items-center gap-1">
                        Built with <span className="text-red-500">❤️</span> by Things at Web
                    </p>
                </div>
            </div>
        </footer>
    );
}

function SocialLink({ href, icon }: { href: string, icon: React.ReactNode }) {
    return (
        <Link
            href={href}
            target="_blank"
            className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-blue-600 hover:text-white transition-all transform hover:scale-110 shadow-sm"
        >
            {icon}
        </Link>
    );
}

