import { Button } from "@/components/ui/button";
import { DownloadCloud, KeyRound } from "lucide-react";
import Link from "next/link";

export const metadata = {
    title: "Download | ETIM Pro",
    description: "Download the ETIM Pro WooCommerce plugin.",
};

export default function DownloadPage() {
    return (
        <div className="pt-24 pb-32">
            <div className="container mx-auto max-w-4xl text-center">
                <h1 className="text-4xl md:text-5xl font-bold tracking-tight mb-4">Download ETIM Pro</h1>
                <p className="text-xl text-muted-foreground mb-16 max-w-2xl mx-auto">
                    Get the latest version of the ETIM Pro WooCommerce plugin.
                </p>

                <div className="bg-background border rounded-3xl p-8 md:p-12 shadow-sm max-w-2xl mx-auto flex flex-col items-center">
                    <div className="h-20 w-20 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-6">
                        <DownloadCloud className="h-10 w-10" />
                    </div>
                    <h2 className="text-2xl font-semibold mb-2">ETIM Pro v1.4.2</h2>
                    <p className="text-muted-foreground text-sm mb-8">Requires WordPress 6.0+ and WooCommerce 8.0+</p>

                    <div className="w-full flex flex-col gap-4">
                        <Button size="lg" className="h-14 w-full text-base rounded-full shadow-lg gap-2">
                            <DownloadCloud className="h-5 w-5" /> Download Latest .zip
                        </Button>
                        <Button size="lg" variant="outline" className="h-14 w-full text-base rounded-full gap-2">
                            <KeyRound className="h-5 w-5" /> Manage Licenses
                        </Button>
                    </div>

                    <div className="mt-8 pt-8 border-t w-full text-sm text-muted-foreground text-center">
                        Need an older version? <Link href="/changelog" className="text-primary hover:underline">View Changelog</Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
