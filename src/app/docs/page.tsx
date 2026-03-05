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
                <h1 className="text-4xl md:text-5xl font-bold tracking-tight mb-4">ETIM Pro Documentation</h1>
                <p className="text-xl text-muted-foreground mb-16 max-w-2xl">
                    Everything you need to master ETIM classification on your WooCommerce store.
                </p>

                <div className="grid md:grid-cols-2 gap-8">
                    {sections.map((section, idx) => (
                        <Card key={idx} className="border bg-background hover:shadow-md transition-shadow">
                            <CardHeader>
                                <div className="flex items-center gap-3 mb-2">
                                    <div className="p-2 bg-primary/10 rounded-lg text-primary">
                                        <section.icon className="h-5 w-5" />
                                    </div>
                                    <CardTitle className="text-xl">{section.title}</CardTitle>
                                </div>
                                <p className="text-muted-foreground text-sm">{section.description}</p>
                            </CardHeader>
                            <CardContent>
                                <ul className="space-y-3">
                                    {section.links.map((link, i) => (
                                        <li key={i}>
                                            <Link href={link.href} className="text-primary hover:underline font-medium text-sm">
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
