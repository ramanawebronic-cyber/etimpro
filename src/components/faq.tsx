import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "@/components/ui/accordion";

export function FAQSection() {
    const faqs = [
        {
            question: "What is ETIM and why does my WooCommerce store need it?",
            answer: "ETIM (European Technical Information Model) is an internationally recognized standard for classifying technical products. It enables B2B companies to exchange product data uniformly across borders and languages. Adding ETIM to your WooCommerce store means your customers can search, compare, and filter products using standardized technical specifications \u2014 dramatically improving the buying experience for distributors and wholesalers.",
        },
        {
            question: "How does the CSV/XML import work?",
            answer: "Simply upload your supplier\u2019s CSV or XML file through our intuitive drag-and-drop interface. ETIM Pro automatically detects the column structure and suggests mappings to ETIM groups, classes, and features. You can adjust mappings visually, save templates for repeated imports, and process files containing thousands of products via smart chunking \u2014 no technical skills required.",
        },
        {
            question: "Will ETIM Pro conflict with my existing WooCommerce products and categories?",
            answer: "Not at all. ETIM Pro is designed to work alongside your existing WooCommerce setup. It creates its own taxonomy layer for ETIM classifications without overwriting your current categories, tags, or attributes. You retain full control over your existing product structure while adding rich ETIM data on top.",
        },
        {
            question: "How does multi-language support work?",
            answer: "ETIM Pro supports up to 17 official ETIM languages. When you assign an ETIM class to a product, the plugin automatically pulls the correct translations for class names, feature descriptions, and values. Your international customers see product specifications in their own language, with a configurable fallback language for any missing translations.",
        },
        {
            question: "Can I bulk-assign ETIM classes to hundreds of products at once?",
            answer: "Yes! ETIM Pro includes powerful bulk assignment tools. You can select entire WooCommerce categories and map them to specific ETIM classes in one operation. The plugin also supports batch processing for large catalogs, letting you classify thousands of products efficiently without slowing down your store.",
        },
        {
            question: "What happens when my license expires?",
            answer: "Your existing ETIM data and classifications remain fully intact \u2014 nothing gets deleted. However, you\u2019ll lose access to plugin updates, new ETIM standard releases, priority support, and cloud-based feature generation. You can renew anytime to restore full functionality.",
        },
        {
            question: "Is there a free trial or demo available?",
            answer: "We offer a 14-day free trial of the Distributor plan so you can test all features with your actual WooCommerce data. No credit card required. You can also request a personalized demo through our contact page where our team walks you through the plugin with your specific use case.",
        },
        {
            question: "What kind of support do you provide?",
            answer: "All plans include email support with guaranteed response times. The Distributor plan includes priority support with faster response. The WooCommerce Pro plan comes with a dedicated account manager, direct Slack channel access, and custom development consultation for complex integration needs.",
        },
    ];

    return (
        <section className="py-24 bg-slate-50/50">
            <div className="container mx-auto max-w-4xl px-4 md:px-6">
                <div className="text-center mb-16">
                    <span className="inline-block text-sm font-semibold text-blue-600 uppercase tracking-wider mb-3">
                        FAQ
                    </span>
                    <h2 className="text-3xl md:text-5xl font-bold tracking-tight text-slate-900 mb-4">
                        Frequently Asked Questions
                    </h2>
                    <p className="text-lg text-slate-500">
                        Everything you need to know about ETIM Pro for WooCommerce.
                    </p>
                </div>

                <div className="w-full bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <Accordion type="single" collapsible className="w-full">
                        {faqs.map((faq, index) => (
                            <AccordionItem key={index} value={`item-${index}`} className="border-b border-slate-100 last:border-0">
                                <AccordionTrigger className="text-left text-base md:text-lg font-medium text-slate-800 hover:text-blue-600 transition-colors py-5 px-6">
                                    {faq.question}
                                </AccordionTrigger>
                                <AccordionContent className="text-slate-500 text-base leading-relaxed pb-5 px-6">
                                    {faq.answer}
                                </AccordionContent>
                            </AccordionItem>
                        ))}
                    </Accordion>
                </div>
            </div>
        </section>
    );
}
