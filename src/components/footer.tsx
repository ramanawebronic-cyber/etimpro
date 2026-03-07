import Link from "next/link";
import Image from "next/image";
import etimLog from "../../public/etim-log.png";
import { Facebook, Youtube, Instagram, Linkedin, MapPin, Mail } from "lucide-react";

export function Footer() {
    return (
        <footer className="border-t border-slate-200 bg-slate-50">
            <div className="container mx-auto py-10 px-4 md:px-8">
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10">
                    {/* Brand */}
                    <div className="lg:col-span-1">
                        <Link href="/" className="flex items-center space-x-2 mb-8">
                            <Image
                                src={etimLog}
                                alt="ETIM Pro"
                                width={180}
                                height={45}
                                className="h-12 w-auto"
                            />
                        </Link>
                        <p className="text-slate-500 text-sm leading-relaxed mb-8 font-medium">
                            The ultimate product classification extension for WooCommerce. Manage ETIM classes, features, and groups effortlessly with native deep integration.
                        </p>
                        <div className="flex items-center gap-4">
                            <SocialLink href="https://www.facebook.com/thingsatweb" icon={<Facebook className="h-5 w-5" />} />
                            <SocialLink href="https://www.youtube.com/@thingsatweb" icon={<Youtube className="h-5 w-5" />} />
                            <SocialLink href="https://www.instagram.com/thingsatweb/" icon={<Instagram className="h-5 w-5" />} />
                            <SocialLink href="https://www.linkedin.com/company/thingsatweb/" icon={<Linkedin className="h-5 w-5" />} />
                        </div>
                    </div>

                    {/* Product */}
                    <div>
                        <h4 className="font-black text-slate-900 mb-6 uppercase tracking-widest text-[10px]">Product</h4>
                        <ul className="space-y-4 text-sm text-slate-500 font-bold">
                            <li><Link href="/features" className="hover:text-blue-600 transition-colors">Features</Link></li>
                            <li><Link href="/pricing" className="hover:text-blue-600 transition-colors">Pricing</Link></li>
                            <li><Link href="/changelog" className="hover:text-blue-600 transition-colors">Changelog</Link></li>
                            <li><Link href="/download" className="hover:text-blue-600 transition-colors">Download</Link></li>
                        </ul>
                    </div>

                    {/* Resources */}
                    <div>
                        <h4 className="font-black text-slate-900 mb-6 uppercase tracking-widest text-[10px]">Resources</h4>
                        <ul className="space-y-4 text-sm text-slate-500 font-bold">
                            <li><Link href="/docs" className="hover:text-blue-600 transition-colors">Documentation</Link></li>
                            <li><Link href="/docs/api" className="hover:text-blue-600 transition-colors">API Reference</Link></li>
                            <li><Link href="/blog" className="hover:text-blue-600 transition-colors">Blog</Link></li>
                            <li><Link href="/community" className="hover:text-blue-600 transition-colors">Community</Link></li>
                        </ul>
                    </div>

                    {/* Legal */}
                    <div>
                        <h4 className="font-black text-slate-900 mb-6 uppercase tracking-widest text-[10px]">Legal</h4>
                        <ul className="space-y-4 text-sm text-slate-500 font-bold">
                            <li><Link href="/privacy" className="hover:text-blue-600 transition-colors">Privacy Policy</Link></li>
                            <li><Link href="/terms" className="hover:text-blue-600 transition-colors">Terms of Service</Link></li>
                            <li><Link href="/contact" className="hover:text-blue-600 transition-colors">Contact Us</Link></li>
                        </ul>
                    </div>

                    {/* Get in Touch */}
                    <div>
                        <h4 className="font-black text-slate-900 mb-6 uppercase tracking-widest text-[10px]">Get in Touch</h4>
                        <div className="space-y-6">
                            <a
                                href="https://www.google.com/maps/place/Sockerbruksgatan+7,+531+40+Lidk%C3%B6ping,+Sweden/@58.5032049,13.1775654,17z/data=!3m1!4b1!4m6!3m5!1s0x465b28a468894b7d:0xd1df8b4fbd46a4e5!8m2!3d58.5032049!4d13.1775654!16s%2Fg%2F11c4wx0mt4?entry=tts&g_ep=EgoyMDI0MTIwOS4wIPu8ASoASAFQAw%3D%3D"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-start gap-4 text-sm text-slate-500 group"
                            >
                                <div className="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                    <MapPin className="h-5 w-5" />
                                </div>
                                <span className="font-bold group-hover:text-blue-600 transition-colors leading-relaxed">Sockerbruksgatan 7<br />53140 Lidköping, Sweden</span>
                            </a>
                            <div className="flex items-center gap-4 text-sm text-slate-500 group">
                                <div className="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                    <Mail className="h-5 w-5" />
                                </div>
                                <a href="mailto:support@etimpro.ai" className="hover:text-blue-600 transition-all font-bold">
                                    support@etimpro.ai
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="border-t border-slate-200 mt-20 py-10 flex border-opacity-50 justify-between items-center text-sm">
                    <p className="text-slate-400 font-bold">&copy; 2026 Things at Web Sweden AB. All Rights Reserved.</p>
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

