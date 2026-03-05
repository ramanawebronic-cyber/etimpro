import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Check } from "lucide-react";
import Link from "next/link";

export function PricingSection() {
    const plans = [
        {
            name: "Manufacturer",
            price: "$199",
            description: "Perfect for single-brand manufacturers.",
            features: [
                "Up to 5 products on frontend",
                "ETIM Classification mapping",
                "CSV Data import",
                "1 Language mapping",
                "1 Year Support"
            ],
            popular: false,
        },
        {
            name: "Distributor",
            price: "$399",
            description: "For multi-brand distributors & B2B.",
            features: [
                "Up to 10 products on frontend",
                "Advanced ETIM Classification",
                "XML & CSV Import",
                "Up to 3 Languages mapping",
                "Priority Support",
                "Bulk Feature Assignment"
            ],
            popular: true,
        },
        {
            name: "WooCommerce Pro",
            price: "$699",
            description: "Enterprise scale WooCommerce catalogs.",
            features: [
                "Unlimited products on frontend",
                "Full Taxonomy Integration",
                "Direct API Integration (beta)",
                "All 17 Languages Supported",
                "Dedicated Account Manager",
                "Custom development discount"
            ],
            popular: false,
        }
    ];

    return (
        <section className="py-24">
            <div className="container px-4 md:px-6">
                <div className="flex flex-col items-center justify-center space-y-4 text-center mb-16">
                    <div className="space-y-2 max-w-3xl">
                        <h2 className="text-3xl font-bold tracking-tighter sm:text-5xl">Simple, transparent pricing</h2>
                        <p className="max-w-[700px] text-muted-foreground md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                            Choose the plan that fits your business needs. Upgrade anytime as your catalog grows.
                        </p>
                    </div>
                </div>
                <div className="mx-auto grid max-w-5xl grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    {plans.map((plan, i) => (
                        <Card key={i} className={`flex flex-col rounded-3xl ${plan.popular ? 'border-primary shadow-lg scale-105 z-10' : 'border-muted'}`}>
                            <CardHeader className="pt-8 px-8">
                                {plan.popular && (
                                    <div className="w-full flex justify-end -mt-4 mb-2">
                                        <span className="bg-primary text-primary-foreground text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                            Most Popular
                                        </span>
                                    </div>
                                )}
                                <CardTitle className="text-2xl">{plan.name}</CardTitle>
                                <CardDescription className="pt-2 text-base">{plan.description}</CardDescription>
                                <div className="mt-4 flex items-baseline text-5xl font-extrabold">
                                    {plan.price}
                                    <span className="ml-1 text-xl font-medium text-muted-foreground">/year</span>
                                </div>
                            </CardHeader>
                            <CardContent className="flex-1 px-8 pt-6">
                                <ul className="grid gap-4">
                                    {plan.features.map((feature, idx) => (
                                        <li key={idx} className="flex items-start gap-3 text-sm">
                                            <Check className="h-5 w-5 text-primary shrink-0" />
                                            <span className="text-muted-foreground">{feature}</span>
                                        </li>
                                    ))}
                                </ul>
                            </CardContent>
                            <CardFooter className="px-8 pb-8 pt-4">
                                <Button className="w-full h-12 rounded-full text-base font-medium" variant={plan.popular ? "default" : "outline"} asChild>
                                    <Link href="/checkout">
                                        {plan.popular ? "Get Started Now" : "Choose Plan"}
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
