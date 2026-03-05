import Link from "next/link";
import { Github, Twitter, Linkedin } from "lucide-react";

export function Footer() {
    return (
        <footer className="border-t bg-muted/20">
            <div className="container mx-auto py-12 md:py-16">
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
                    <div className="col-span-2 lg:col-span-2">
                        <Link href="/" className="flex items-center space-x-2 mb-4">
                            <span className="font-bold text-xl inline-block bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                                ETIM Pro
                            </span>
                        </Link>
                        <p className="text-muted-foreground text-sm mb-6 max-w-xs">
                            The ultimate product classification extension for WooCommerce. Manage ETIM classes, features, and groups effortlessly.
                        </p>
                        <div className="flex items-center gap-4 text-muted-foreground">
                            <Link href="#" className="hover:text-primary transition-colors"><Twitter className="h-5 w-5" /></Link>
                            <Link href="#" className="hover:text-primary transition-colors"><Github className="h-5 w-5" /></Link>
                            <Link href="#" className="hover:text-primary transition-colors"><Linkedin className="h-5 w-5" /></Link>
                        </div>
                    </div>
                    <div>
                        <h4 className="font-semibold mb-4">Product</h4>
                        <ul className="grid gap-2 text-sm text-muted-foreground">
                            <li><Link href="/features" className="hover:text-primary transition-colors">Features</Link></li>
                            <li><Link href="/pricing" className="hover:text-primary transition-colors">Pricing</Link></li>
                            <li><Link href="/changelog" className="hover:text-primary transition-colors">Changelog</Link></li>
                            <li><Link href="/download" className="hover:text-primary transition-colors">Download</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 className="font-semibold mb-4">Resources</h4>
                        <ul className="grid gap-2 text-sm text-muted-foreground">
                            <li><Link href="/docs" className="hover:text-primary transition-colors">Documentation</Link></li>
                            <li><Link href="/docs/api" className="hover:text-primary transition-colors">API Reference</Link></li>
                            <li><Link href="/blog" className="hover:text-primary transition-colors">Blog</Link></li>
                            <li><Link href="/community" className="hover:text-primary transition-colors">Community</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 className="font-semibold mb-4">Legal</h4>
                        <ul className="grid gap-2 text-sm text-muted-foreground">
                            <li><Link href="/privacy" className="hover:text-primary transition-colors">Privacy Policy</Link></li>
                            <li><Link href="/terms" className="hover:text-primary transition-colors">Terms of Service</Link></li>
                            <li><Link href="/contact" className="hover:text-primary transition-colors">Contact Us</Link></li>
                        </ul>
                    </div>
                </div>
                <div className="border-t mt-12 py-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-muted-foreground">
                    <p>© {new Date().getFullYear()} ETIM Pro. All rights reserved.</p>
                    <p>Built for modern WooCommerce stores.</p>
                </div>
            </div>
        </footer>
    );
}
