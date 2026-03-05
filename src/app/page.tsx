import { HeroSection } from "@/components/hero";
import { FeaturesSection } from "@/components/features";
import { DashboardPreview } from "@/components/dashboard-preview";
import { PricingSection } from "@/components/pricing";
import { FAQSection } from "@/components/faq";
import { Button } from "@/components/ui/button";
import Link from "next/link";
import { ArrowRight } from "lucide-react";
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'ETIM Pro | WooCommerce Product Classification Plugin',
  description: 'The ultimate WooCommerce plugin for ETIM group, class and feature assignment. Import CSV/XML data easily and map up to 17 languages.',
};

export default function Home() {
  return (
    <>
      <HeroSection />

      <DashboardPreview />

      {/* Social Proof */}
      <section className="py-12 border-y bg-muted/20">
        <div className="container text-center">
          <p className="text-sm font-medium text-muted-foreground uppercase tracking-wider mb-8">
            Trusted by modern B2B electrical & plumbing distributors
          </p>
          <div className="flex flex-wrap justify-center gap-x-12 gap-y-8 opacity-50 grayscale hover:grayscale-0 transition-all">
            {/* Placeholder logos for social proof */}
            <div className="text-2xl font-bold font-serif opacity-80 hover:text-primary transition-colors">Vanderbilt</div>
            <div className="text-2xl font-bold font-sans italic opacity-80 hover:text-primary transition-colors">LuminaTech</div>
            <div className="text-2xl font-bold opacity-80 hover:text-primary transition-colors">NexCable</div>
            <div className="text-2xl font-extrabold opacity-80 hover:text-primary transition-colors">PlumbCorp</div>
            <div className="text-2xl font-bold font-mono opacity-80 hover:text-primary transition-colors">ElecPro</div>
          </div>
        </div>
      </section>

      <FeaturesSection />

      <div id="pricing">
        <PricingSection />
      </div>

      <FAQSection />

      {/* Bottom CTA */}
      <section className="py-24 relative overflow-hidden">
        <div className="absolute inset-0 bg-primary/5 rounded-[3rem] mx-4 md:mx-auto max-w-6xl -z-10"></div>
        <div className="container text-center">
          <h2 className="text-3xl md:text-5xl font-bold tracking-tight mb-4">
            Ready to structure your catalog?
          </h2>
          <p className="text-xl text-muted-foreground mb-8 max-w-2xl mx-auto">
            Stop relying on basic categories. Bring the power of ETIM standard directly into WooCommerce.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button size="lg" asChild className="h-14 px-8 text-base shadow-lg hover:shadow-primary/20 transition-all rounded-full">
              <Link href="/pricing">
                Get Started
                <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
            </Button>
            <Button size="lg" variant="outline" asChild className="h-14 px-8 text-base bg-background rounded-full">
              <Link href="/contact">
                Talk to Sales
              </Link>
            </Button>
          </div>
        </div>
      </section>
    </>
  );
}
