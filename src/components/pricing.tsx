import { Button } from "@/components/ui/button";
import { Check } from "lucide-react";
import Link from "next/link";
import Image from "next/image";
import freeImg from "../../public/free.png";
import standardImg from "../../public/standard.png";
import proImg from "../../public/pro.png";

export function PricingSection() {
    const plans = [
        {
            name: "Manufacturer",
            icon: <Image src={freeImg} alt="Manufacturer Plan" width={64} height={64} className="mx-auto mb-6 object-contain h-16 w-auto" />,
            price: "$299",
            oldPrice: "$599",
            discount: "50% OFF",
            period: "/ year",
            description: "Best for manufacturers getting started with ETIM classification",
            features: [
                "Assign ETIM data to up to 5 products",
                "Dynamic Feature Generation",
                "CSV Import support",
                "Maximum 1 website license",
                "Bulk assignment: 10 products/batch"
            ],
            tag: null,
            tagColor: "",
            borderColor: "border-gray-200",
            titleColor: "text-black",
            priceColor: "text-black",
            buttonText: "Select Plan",
            buttonClasses: "bg-black text-white hover:bg-gray-800",
        },
        {
            name: "Distributor",
            icon: <Image src={standardImg} alt="Distributor Plan" width={64} height={64} className="mx-auto mb-6 object-contain h-16 w-auto" />,
            price: "$599",
            oldPrice: "$999",
            discount: "40% OFF",
            period: "/ year",
            description: "Perfect for distributors and wholesalers managing larger product catalogs",
            features: [
                "Everything in Manufacturer plan",
                "Assign ETIM data to up to 50 products",
                "ETIM Class Filtering option",
                "CSV & XML Import / Export",
                "Maximum 5 website licenses",
                "Bulk assignment: 50 products/batch"
            ],
            tag: "MOST POPULAR",
            tagColor: "bg-blue-500 text-white",
            borderColor: "border-blue-500",
            titleColor: "text-blue-500",
            priceColor: "text-blue-500",
            buttonText: "Current Plan",
            buttonClasses: "bg-blue-500 text-white hover:bg-blue-600",
        },
        {
            name: "WooCommerce Agency",
            icon: <Image src={proImg} alt="WooCommerce Agency Plan" width={64} height={64} className="mx-auto mb-6 object-contain h-16 w-auto" />,
            price: "$999",
            oldPrice: "$1999",
            discount: "50% OFF",
            period: "/ year",
            description: "Ideal for agencies managing multiple WooCommerce stores",
            features: [
                "Everything in Distributor plan",
                "Unlimited ETIM data assignments",
                "Maximum 10 website licenses",
                "Unlimited bulk product assignments",
                "Multilingual support"
            ],
            tag: "PREMIUM",
            tagColor: "bg-amber-600 text-white",
            borderColor: "border-amber-600",
            titleColor: "text-amber-600",
            priceColor: "text-amber-600",
            buttonText: "Select Plan",
            buttonClasses: "bg-amber-600 text-white hover:bg-amber-700",
        }
    ];

    return (
        <section className="py-24 bg-white">
            <div className="container mx-auto px-4 md:px-6">
                <div className="flex flex-col items-center justify-center space-y-4 text-center mb-16">
                    <div className="space-y-2 max-w-3xl">
                        <span className="inline-block text-sm font-semibold text-blue-600 uppercase tracking-wider mb-1">
                            Pricing
                        </span>
                        <h2 className="text-3xl font-bold tracking-tighter sm:text-5xl text-slate-900">Simple, transparent pricing</h2>
                        <p className="max-w-[700px] text-slate-500 md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                            Choose the plan that fits your business needs. Upgrade anytime as your catalog grows.
                        </p>
                    </div>
                </div>

                <div className="mx-auto grid max-w-6xl grid-cols-1 gap-6 md:grid-cols-3 items-stretch">
                    {plans.map((plan, i) => (
                        <div key={i} className={`flex flex-col rounded-xl border ${plan.borderColor} bg-white overflow-hidden relative shadow-sm`}>
                            {plan.tag && (
                                <div className={`absolute top-0 right-0 ${plan.tagColor} text-xs font-black px-4 py-1.5 rounded-bl-xl tracking-wide z-10 uppercase`}>
                                    {plan.tag}
                                </div>
                            )}

                            <div className="p-8 pb-4 flex-1">
                                <div className="mt-4">
                                    {plan.icon}
                                </div>
                                <h3 className={`text-2xl font-black mb-2 ${plan.titleColor}`}>{plan.name}</h3>
                                <p className="text-sm text-slate-500 mb-8 h-10">{plan.description}</p>

                                <div className="mb-4">
                                    <div className="flex items-baseline mb-2">
                                        <span className={`text-5xl font-black ${plan.priceColor}`}>{plan.price}</span>
                                        <span className="text-sm font-medium text-slate-600 ml-1">{plan.period}</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="text-sm text-slate-400 line-through font-medium">{plan.oldPrice}</span>
                                        <span className="bg-emerald-50 text-emerald-600 text-xs font-bold px-2 py-0.5 rounded-md">
                                            {plan.discount}
                                        </span>
                                    </div>
                                </div>

                                <ul className="space-y-3 mt-8">
                                    {plan.features.map((feature, idx) => (
                                        <li key={idx} className="flex items-start gap-3">
                                            <div className="rounded-full bg-blue-500 p-0.5 mt-0.5 shrink-0">
                                                <Check className="h-3 w-3 text-white" strokeWidth={3} />
                                            </div>
                                            <span className="text-sm text-slate-600 font-medium leading-relaxed">{feature}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            <div className="p-6 pt-0 mt-auto">
                                <Button
                                    className={`w-full h-12 rounded-xl text-base font-bold transition-all ${plan.buttonClasses}`}
                                    asChild
                                >
                                    <Link href="/checkout">
                                        {plan.buttonText}
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
