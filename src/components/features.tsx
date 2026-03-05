import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Database, Download, Globe, Layers, Link as LinkIcon, ReplaceAll } from "lucide-react";

export function FeaturesSection() {
    const features = [
        {
            title: "ETIM Classification Management",
            description: "Assign ETIM groups, classes, and features dynamically to your WooCommerce products with ease.",
            icon: Layers,
            color: "bg-blue-50 text-blue-600",
        },
        {
            title: "Bulk Import Options",
            description: "Import extensive ETIM data via CSV or XML file processing, mapping perfectly to your products.",
            icon: Download,
            color: "bg-indigo-50 text-indigo-600",
        },
        {
            title: "Multi-Language Support",
            description: "Deliver ETIM properties in up to 17 languages seamlessly. A must-have for international wholesalers.",
            icon: Globe,
            color: "bg-emerald-50 text-emerald-600",
        },
        {
            title: "Dynamic Feature Generation",
            description: "Automatically transform numerical or string ETIM data into WooCommerce product attributes.",
            icon: Database,
            color: "bg-violet-50 text-violet-600",
        },
        {
            title: "WooCommerce Integration",
            description: "Native experience within wp-admin, deeply integrated with product management operations.",
            icon: LinkIcon,
            color: "bg-amber-50 text-amber-600",
        },
        {
            title: "Bulk Assignments",
            description: "Perform massive assignments linking complete ETIM classes across hundreds of product categories.",
            icon: ReplaceAll,
            color: "bg-rose-50 text-rose-600",
        },
    ];

    return (
        <section className="py-24 bg-slate-50/50">
            <div className="container mx-auto relative">
                <div className="text-center mb-16 max-w-2xl mx-auto">
                    <span className="inline-block text-sm font-semibold text-blue-600 uppercase tracking-wider mb-3">
                        Features
                    </span>
                    <h2 className="text-3xl md:text-5xl font-bold tracking-tight text-slate-900 mb-4">
                        Everything you need for ETIM
                    </h2>
                    <p className="text-lg text-slate-500">
                        Powerful tools designed specifically for B2B manufacturers and distributors running WooCommerce.
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {features.map((feature, index) => (
                        <Card key={index} className="bg-white border border-slate-200 hover:border-blue-200 transition-all hover:shadow-lg hover:shadow-blue-500/5 rounded-2xl overflow-hidden group">
                            <CardHeader>
                                <div className={`h-12 w-12 rounded-xl ${feature.color} flex items-center justify-center mb-4 group-hover:scale-110 transition-transform`}>
                                    <feature.icon className="h-6 w-6" />
                                </div>
                                <CardTitle className="text-xl text-slate-900">{feature.title}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription className="text-base text-slate-500 leading-relaxed">
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
