import { Badge } from "@/components/ui/badge";

export const metadata = {
    title: "Changelog | ETIM Pro",
    description: "Recent updates and improvements to the ETIM Pro WooCommerce plugin.",
};

export default function ChangelogPage() {
    const versions = [
        {
            version: "v1.4.2",
            date: "March 5, 2026",
            type: "Feature",
            changes: [
                "Added multi-language support (17 languages)",
                "Improved CSV import performance by 40%",
                "Fixed minor UI bugs in the feature mapping tab"
            ]
        },
        {
            version: "v1.3.0",
            date: "February 12, 2026",
            type: "Update",
            changes: [
                "Added XML bulk import support via external URLs",
                "Introduced license management UI",
                "Frontend table filter styling options added",
                "Resolved conflict with custom variations plugin"
            ]
        },
        {
            version: "v1.2.5",
            date: "January 20, 2026",
            type: "Bugfix",
            changes: [
                "Fixed issue where specific ETIM classes would not sync on low-memory servers",
                "Updated translation strings",
                "Optimized database queries for the product assignment table"
            ]
        }
    ];

    return (
        <div className="pt-24 pb-32">
            <div className="container mx-auto max-w-3xl">
                <h1 className="text-4xl md:text-5xl font-bold tracking-tight text-slate-900 mb-4">Changelog</h1>
                <p className="text-xl text-slate-500 mb-16">
                    See what&apos;s new in ETIM Pro.
                </p>

                <div className="space-y-12 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-blue-200 before:to-transparent">
                    {versions.map((ver, idx) => (
                        <div key={idx} className="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                            <div className="flex items-center justify-center w-10 h-10 rounded-full border-2 border-white bg-blue-600 text-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                                <div className="w-2 h-2 rounded-full bg-white"></div>
                            </div>
                            <div className="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-6 rounded-2xl border border-slate-200 bg-white hover:shadow-md transition-shadow">
                                <div className="flex items-center justify-between mb-2 pb-2 border-b border-slate-100">
                                    <div className="font-bold text-xl text-slate-900">{ver.version}</div>
                                    <div className="text-sm text-slate-400">{ver.date}</div>
                                </div>
                                <Badge className={`mb-4 ${ver.type === 'Feature' ? 'bg-blue-50 text-blue-600' : ver.type === 'Update' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'} border-0`}>
                                    {ver.type}
                                </Badge>
                                <ul className="space-y-2 text-slate-500 text-sm list-disc pl-4">
                                    {ver.changes.map((change, i) => <li key={i}>{change}</li>)}
                                </ul>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
