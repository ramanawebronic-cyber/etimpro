import { HeroSection } from "@/components/hero";
import { FeaturesSection } from "@/components/features";
import { DashboardPreview } from "@/components/dashboard-preview";
import { HowItWorksSection } from "@/components/how-it-works";
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
      <section className="py-12 border-y border-slate-100 bg-slate-50/50">
        <div className="container mx-auto text-center">
          <p className="text-sm font-medium text-slate-400 uppercase tracking-wider mb-8">
            Trusted by modern B2B electrical & plumbing distributors
          </p>
          <div className="flex flex-wrap justify-center gap-x-12 gap-y-8 opacity-60 grayscale hover:grayscale-0 transition-all">
            <div className="text-2xl font-bold font-serif text-slate-500 hover:text-blue-600 transition-colors">Vanderbilt</div>
            <div className="text-2xl font-bold font-sans italic text-slate-500 hover:text-blue-600 transition-colors">LuminaTech</div>
            <div className="text-2xl font-bold text-slate-500 hover:text-blue-600 transition-colors">NexCable</div>
            <div className="text-2xl font-extrabold text-slate-500 hover:text-blue-600 transition-colors">PlumbCorp</div>
            <div className="text-2xl font-bold font-mono text-slate-500 hover:text-blue-600 transition-colors">ElecPro</div>
          </div>
        </div>
      </section>

      <FeaturesSection />

      <HowItWorksSection />

      <div id="pricing">
        <PricingSection />
      </div>

      <FAQSection />

      {/* Bottom CTA */}
      <section className="py-24 relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-[3rem] mx-4 md:mx-auto max-w-6xl -z-10"></div>
        <div className="container mx-auto text-center">
          <h2 className="text-3xl md:text-5xl font-bold tracking-tight text-slate-900 mb-4">
            Ready to structure your catalog?
          </h2>
          <p className="text-xl text-slate-500 mb-8 max-w-2xl mx-auto">
            Stop relying on basic categories. Bring the power of ETIM standard directly into WooCommerce.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button size="lg" asChild className="h-14 px-8 text-base shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all rounded-full bg-blue-600 hover:bg-blue-700 text-white">
              <Link href="/pricing">
                Get Started
                <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
            </Button>
            <Button size="lg" variant="outline" asChild className="h-14 px-8 text-base bg-white border-slate-200 text-slate-700 hover:bg-slate-50 rounded-full">
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
