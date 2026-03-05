import { FeaturesSection } from "@/components/features";
import { Badge } from "@/components/ui/badge";

export const metadata = {
    title: "Features | ETIM Pro",
    description: "Explore all features of the ETIM Pro WooCommerce plugin.",
};

export default function FeaturesPage() {
    return (
        <div className="pt-24 pb-16">
            <div className="container max-w-4xl text-center mb-16">
                <Badge variant="outline" className="mb-6 rounded-full px-4 py-1.5 bg-primary/10 text-primary border-primary/20">
                    Powerful Capabilites
                </Badge>
                <h1 className="text-4xl md:text-6xl font-extrabold tracking-tight mb-6">
                    Everything you need to manage ETIM data
                </h1>
                <p className="text-xl text-muted-foreground">
                    Built from the ground up to integrate perfectly with WooCommerce. ETIM Pro offers the most robust set of tools for technical product catalogs on WordPress.
                </p>
            </div>

            <FeaturesSection />

            <div className="container max-w-6xl mt-24">
                {/* Additional detailed feature blocks can go here */}
                <div className="grid md:grid-cols-2 gap-12 items-center mb-24">
                    <div className="bg-muted aspect-video rounded-3xl border shadow-sm"></div>
                    <div>
                        <h3 className="text-3xl font-bold mb-4">CSV Data Mapping</h3>
                        <p className="text-muted-foreground text-lg mb-6">
                            Don&apos;t struggle with manual entry. Upload your suppliers&apos; CSV files and map their columns directly to ETIM classes and WooCommerce product data.
                        </p>
                        <ul className="space-y-3">
                            <li className="flex items-center gap-3"><div className="w-1.5 h-1.5 rounded-full bg-primary" /> Visual mapping interface</li>
                            <li className="flex items-center gap-3"><div className="w-1.5 h-1.5 rounded-full bg-primary" /> Support for massive files via chunking</li>
                            <li className="flex items-center gap-3"><div className="w-1.5 h-1.5 rounded-full bg-primary" /> Mapping templates for repeated imports</li>
                        </ul>
                    </div>
                </div>

                <div className="grid md:grid-cols-2 gap-12 items-center">
                    <div className="order-2 md:order-1">
                        <h3 className="text-3xl font-bold mb-4">Multi-language Support</h3>
                        <p className="text-muted-foreground text-lg mb-6">
                            Operating in multiple countries? ETIM Pro pulls official ETIM translation data in up to 17 languages, ensuring your descriptions are always accurate locally.
                        </p>
                        <ul className="space-y-3">
                            <li className="flex items-center gap-3"><div className="w-1.5 h-1.5 rounded-full bg-primary" /> Official ETIM descriptions</li>
                            <li className="flex items-center gap-3"><div className="w-1.5 h-1.5 rounded-full bg-primary" /> Seamless Frontend Display</li>
                            <li className="flex items-center gap-3"><div className="w-1.5 h-1.5 rounded-full bg-primary" /> Fallback language support</li>
                        </ul>
                    </div>
                    <div className="order-1 md:order-2 bg-muted aspect-video rounded-3xl border shadow-sm"></div>
                </div>
            </div>
        </div>
    );
}
