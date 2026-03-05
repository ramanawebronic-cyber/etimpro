import { Download, Upload, Settings, Zap, CheckCircle2, ArrowRight } from "lucide-react";

export function HowItWorksSection() {
    const steps = [
        {
            step: "01",
            title: "Install the Plugin",
            description: "Download and activate ETIM Pro directly from your WordPress admin panel. One-click setup, no coding required.",
            icon: Download,
            color: "bg-blue-50 text-blue-600 border-blue-100",
        },
        {
            step: "02",
            title: "Import Your ETIM Data",
            description: "Upload your CSV or XML files containing ETIM classifications. Our smart mapper auto-detects columns and matches them to ETIM groups, classes, and features.",
            icon: Upload,
            color: "bg-indigo-50 text-indigo-600 border-indigo-100",
        },
        {
            step: "03",
            title: "Configure & Map Products",
            description: "Use the visual interface to assign ETIM classes to your WooCommerce products. Bulk-assign entire categories or fine-tune individual products.",
            icon: Settings,
            color: "bg-violet-50 text-violet-600 border-violet-100",
        },
        {
            step: "04",
            title: "Generate Features Automatically",
            description: "ETIM Pro dynamically generates product attributes from your ETIM data, creating rich, standardized product information across all languages.",
            icon: Zap,
            color: "bg-amber-50 text-amber-600 border-amber-100",
        },
        {
            step: "05",
            title: "Publish & Go Live",
            description: "Your WooCommerce store now displays fully classified, multi-language ETIM data. Customers can filter and find products using standardized technical specifications.",
            icon: CheckCircle2,
            color: "bg-emerald-50 text-emerald-600 border-emerald-100",
        },
    ];

    return (
        <section className="py-24 bg-white">
            <div className="container mx-auto px-4 md:px-6">
                <div className="text-center mb-16 max-w-2xl mx-auto">
                    <span className="inline-block text-sm font-semibold text-blue-600 uppercase tracking-wider mb-3">
                        How It Works
                    </span>
                    <h2 className="text-3xl md:text-5xl font-bold tracking-tight text-slate-900 mb-4">
                        From install to live in 5 simple steps
                    </h2>
                    <p className="text-lg text-slate-500">
                        Get your WooCommerce store fully ETIM-classified in minutes, not months.
                    </p>
                </div>

                {/* Desktop: Timeline layout */}
                <div className="max-w-4xl mx-auto">
                    {steps.map((item, index) => (
                        <div key={index} className="relative flex gap-6 md:gap-10 pb-12 last:pb-0">
                            {/* Timeline line */}
                            {index < steps.length - 1 && (
                                <div className="absolute left-[27px] md:left-[31px] top-[56px] w-0.5 h-[calc(100%-32px)] bg-gradient-to-b from-blue-200 to-blue-50" />
                            )}

                            {/* Icon */}
                            <div className={`relative z-10 shrink-0 w-14 h-14 md:w-16 md:h-16 rounded-2xl border-2 ${item.color} flex items-center justify-center shadow-sm`}>
                                <item.icon className="h-6 w-6 md:h-7 md:w-7" />
                            </div>

                            {/* Content */}
                            <div className="flex-1 pt-1">
                                <div className="flex items-center gap-3 mb-2">
                                    <span className="text-xs font-bold text-blue-500 uppercase tracking-widest">Step {item.step}</span>
                                </div>
                                <h3 className="text-xl md:text-2xl font-bold text-slate-900 mb-2">
                                    {item.title}
                                </h3>
                                <p className="text-slate-500 leading-relaxed max-w-xl">
                                    {item.description}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Bottom CTA */}
                <div className="text-center mt-16">
                    <div className="inline-flex items-center gap-2 text-blue-600 font-semibold hover:gap-3 transition-all cursor-pointer">
                        <span>See it in action</span>
                        <ArrowRight className="h-4 w-4" />
                    </div>
                </div>
            </div>
        </section>
    );
}
