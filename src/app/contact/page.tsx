import { Button } from "@/components/ui/button";
import { Mail, MessageSquare, Phone } from "lucide-react";

export const metadata = {
    title: "Contact | ETIM Pro",
    description: "Get in touch with the ETIM Pro team.",
};

export default function ContactPage() {
    return (
        <div className="pt-24 pb-32">
            <div className="container max-w-4xl text-center">
                <h1 className="text-4xl md:text-5xl font-bold tracking-tight mb-4">Contact Sales & Support</h1>
                <p className="text-xl text-muted-foreground mb-16 max-w-2xl mx-auto">
                    We&apos;re here to help you get the most out of ETIM Pro.
                </p>

                <div className="grid md:grid-cols-3 gap-8 mb-16">
                    <div className="p-8 border rounded-3xl bg-background hover:shadow-md transition-shadow flex flex-col items-center text-center">
                        <Mail className="h-8 w-8 text-primary mb-4" />
                        <h3 className="font-semibold text-lg mb-2">Email Support</h3>
                        <p className="text-muted-foreground text-sm mb-4 flex-1">Reach out to our dedicated support team via email.</p>
                        <a href="mailto:support@etim-pro.com" className="text-primary font-medium text-sm hover:underline">support@etim-pro.com</a>
                    </div>
                    <div className="p-8 border rounded-3xl bg-background hover:shadow-md transition-shadow flex flex-col items-center text-center shadow-lg border-primary/20 scale-105 z-10">
                        <MessageSquare className="h-8 w-8 text-primary mb-4" />
                        <h3 className="font-semibold text-lg mb-2">Live Chat</h3>
                        <p className="text-muted-foreground text-sm mb-4 flex-1">Chat with a classification expert right now.</p>
                        <Button variant="default" className="w-full rounded-full">Start Chat</Button>
                    </div>
                    <div className="p-8 border rounded-3xl bg-background hover:shadow-md transition-shadow flex flex-col items-center text-center">
                        <Phone className="h-8 w-8 text-primary mb-4" />
                        <h3 className="font-semibold text-lg mb-2">Enterprise Sales</h3>
                        <p className="text-muted-foreground text-sm mb-4 flex-1">Discuss custom integrations or large catalogs.</p>
                        <a href="tel:+15551234567" className="text-primary font-medium text-sm hover:underline">+1 (555) 123-4567</a>
                    </div>
                </div>
            </div>
        </div>
    );
}
