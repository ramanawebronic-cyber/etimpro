import { PricingSection } from "@/components/pricing";
import { FAQSection } from "@/components/faq";

export const metadata = {
    title: "Pricing | ETIM Pro",
    description: "Pricing plans for the ETIM Pro WooCommerce plugin.",
};

export default function PricingPage() {
    return (
        <div className="pt-24 pb-16">
            <div className="container mx-auto max-w-4xl text-center mb-16 px-4 md:px-0">
                <h1 className="text-4xl md:text-6xl font-extrabold tracking-tight text-slate-900 mb-6">
                    Simple, transparent pricing
                </h1>
                <p className="text-xl text-slate-500 mb-8">
                    One flat annual fee. No hidden costs. 14-day money-back guarantee.
                </p>
            </div>

            <PricingSection />

            <div className="mt-24">
                <FAQSection />
            </div>
        </div>
    );
}
