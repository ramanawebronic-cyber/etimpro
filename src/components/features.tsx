import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Database, Download, Globe, Layers, Link as LinkIcon, ReplaceAll } from "lucide-react";

export function FeaturesSection() {
    const features = [
        {
            title: "ETIM Classification Management",
            description: "Assign ETIM groups, classes, and features dynamically to your WooCommerce products with ease.",
            icon: Layers,
        },
        {
            title: "Bulk Import Options",
            description: "Import extensive ETIM data via CSV or XML file processing, mapping perfectly to your products.",
            icon: Download,
        },
        {
            title: "Multi-Language Support",
            description: "Deliver ETIM properties in up to 17 languages seamlessly. A must-have for international wholesalers.",
            icon: Globe,
        },
        {
            title: "Dynamic Feature Generation",
            description: "Automatically transform numerical or string ETIM data into WooCommerce product attributes.",
            icon: Database,
        },
        {
            title: "WooCommerce Integration",
            description: "Native experience within wp-admin, deeply integrated with product management operations.",
            icon: LinkIcon,
        },
        {
            title: "Bulk Assignments",
            description: "Perform massive assignments linking complete ETIM classes across hundreds of product categories.",
            icon: ReplaceAll,
        },
    ];

    return (
        <section className="py-24 bg-muted/30">
            <div className="container relative">
                <div className="text-center mb-16 max-w-2xl mx-auto">
                    <h2 className="text-3xl md:text-5xl font-bold tracking-tight mb-4">
                        Everything you need for ETIM
                    </h2>
                    <p className="text-lg text-muted-foreground">
                        Powerful tools designed specifically for B2B manufacturers and distributors running WooCommerce.
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {features.map((feature, index) => (
                        <Card key={index} className="bg-background/60 backdrop-blur-md border border-primary/10 hover:border-primary/30 transition-all hover:shadow-lg dark:hover:shadow-primary/5 rounded-2xl overflow-hidden">
                            <CardHeader>
                                <div className="h-12 w-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                                    <feature.icon className="h-6 w-6" />
                                </div>
                                <CardTitle className="text-xl">{feature.title}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription className="text-base text-muted-foreground leading-relaxed">
                                    {feature.description}
                                </CardDescription>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
