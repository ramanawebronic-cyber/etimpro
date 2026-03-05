import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { BookOpen, Map, Settings, Zap } from "lucide-react";
import Link from "next/link";

export const metadata = {
    title: "Documentation | ETIM Pro",
    description: "Learn how to install, configure, and manage ETIM data in WooCommerce.",
};

export default function DocsPage() {
    const sections = [
        {
            title: "Getting Started",
            description: "Installation, finding your license key, and first steps.",
            icon: BookOpen,
            color: "bg-blue-50 text-blue-600",
            links: [
                { name: "Installation Guide", href: "#" },
                { name: "Activating your License", href: "#" },
                { name: "Basic Configuration", href: "#" }
            ]
        },
        {
            title: "Data Management",
            description: "Importing CSV/XML files and bulk assigning product features.",
            icon: Map,
            color: "bg-emerald-50 text-emerald-600",
            links: [
                { name: "CSV Import Guide", href: "#" },
                { name: "XML Processing", href: "#" },
                { name: "Mapping ETIM Classes", href: "#" }
            ]
        },
        {
            title: "Advanced Features",
            description: "Dynamic feature generation and conditional logic.",
            icon: Zap,
            color: "bg-amber-50 text-amber-600",
            links: [
                { name: "Multi-language Setup", href: "#" },
                { name: "Dynamic Attributes", href: "#" },
                { name: "Performance Tuning", href: "#" }
            ]
        },
        {
            title: "Developer API",
            description: "Hooks, filters, and programmatic interactions.",
            icon: Settings,
            color: "bg-violet-50 text-violet-600",
            links: [
                { name: "Available Hooks", href: "#" },
                { name: "Custom Import Processors", href: "#" },
                { name: "REST API Endpoints", href: "#" }
            ]
        }
    ];

    return (
        <div className="pt-24 pb-32">
            <div className="container mx-auto max-w-5xl">
                <h1 className="text-4xl md:text-5xl font-bold tracking-tight text-slate-900 mb-4">ETIM Pro Documentation</h1>
                <p className="text-xl text-slate-500 mb-16 max-w-2xl">
                    Everything you need to master ETIM classification on your WooCommerce store.
                </p>

                <div className="grid md:grid-cols-2 gap-8">
                    {sections.map((section, idx) => (
                        <Card key={idx} className="border border-slate-200 bg-white hover:shadow-lg hover:shadow-blue-500/5 transition-all rounded-2xl">
                            <CardHeader>
                                <div className="flex items-center gap-3 mb-2">
                                    <div className={`p-2 rounded-lg ${section.color}`}>
                                        <section.icon className="h-5 w-5" />
                                    </div>
                                    <CardTitle className="text-xl text-slate-900">{section.title}</CardTitle>
                                </div>
                                <p className="text-slate-500 text-sm">{section.description}</p>
                            </CardHeader>
                            <CardContent>
                                <ul className="space-y-3">
                                    {section.links.map((link, i) => (
                                        <li key={i}>
                                            <Link href={link.href} className="text-blue-600 hover:underline font-medium text-sm">
                                                {link.name}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </div>
    );
}
