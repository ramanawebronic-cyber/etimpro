import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "@/components/ui/accordion";

export function FAQSection() {
    const faqs = [
        {
            question: "What is ETIM product classification?",
            answer: "ETIM (European Technical Information Model) is the standard for classifying technical products, allowing B2B companies to exchange product data uniformly without language barriers.",
        },
        {
            question: "Will this plugin work with my existing WooCommerce setup?",
            answer: "Yes! ETIM Pro is designed specifically for WooCommerce. It integrates natively with your existing product taxonomy and attribute systems without overwriting your current data layout unless instructed.",
        },
        {
            question: "Do I need technical skills to use the CSV/XML import?",
            answer: "No. The plugin includes an intuitive mapping interface that allows you to visually map columns from your data source to ETIM classifications and WooCommerce attributes.",
        },
        {
            question: "Can I manage multi-language ETIM data?",
            answer: "Absolutely. We support up to 17 standard ETIM languages, allowing you to define your core product data once and serve it internationally to your wholesale clients.",
        },
        {
            question: "What happens if my license expires?",
            answer: "The plugin will still operate, but you will lose access to updates, priority support, and certain cloud-based feature generations until you renew.",
        },
        {
            question: "Is there a limit to how many products I can apply ETIM data to?",
            answer: "Depending on your license tier, there may be a frontend display limit, but backend management and import operations generally support catalog sizes into the tens of thousands. Performance is mostly limited by your server hardware and PHP configuration.",
        }
    ];

    return (
        <section className="py-24 bg-muted/10">
            <div className="container max-w-4xl px-4 md:px-6">
                <div className="text-center mb-16">
                    <h2 className="text-3xl md:text-5xl font-bold tracking-tight mb-4">
                        Frequently Asked Questions
                    </h2>
                    <p className="text-lg text-muted-foreground">
                        Clear answers to help you get started with ETIM Pro on WooCommerce.
                    </p>
                </div>

                <div className="w-full">
                    <Accordion type="single" collapsible className="w-full">
                        {faqs.map((faq, index) => (
                            <AccordionItem key={index} value={`item-${index}`} className="border-b border-primary/10">
                                <AccordionTrigger className="text-left text-base md:text-lg font-medium hover:text-primary transition-colors py-6">
                                    {faq.question}
                                </AccordionTrigger>
                                <AccordionContent className="text-muted-foreground text-base leading-relaxed pb-6">
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
