/* eslint-disable @next/next/no-img-element */
import { FeaturesSection } from "@/components/features";
import { Badge } from "@/components/ui/badge";
import { FileSpreadsheet, ArrowDownToLine, Check, Globe } from "lucide-react";

export const metadata = {
    title: "Features | ETIM Pro",
    description: "Explore all features of the ETIM Pro WooCommerce plugin.",
};

const previewLanguages = [
    { code: "EN", countryCode: "gb", text: "LED Panel Light" },
    { code: "DE", countryCode: "de", text: "LED-Panelleuchte" },
    { code: "FR", countryCode: "fr", text: "Panneau LED" },
    { code: "NL", countryCode: "nl", text: "LED Paneelverlichting" },
    { code: "ES", countryCode: "es", text: "Panel LED" },
];

const allLanguages = [
    { code: "EN", countryCode: "gb" }, { code: "DE", countryCode: "de" },
    { code: "FR", countryCode: "fr" }, { code: "NL", countryCode: "nl" },
    { code: "SV", countryCode: "se" }, { code: "ES", countryCode: "es" },
    { code: "IT", countryCode: "it" }, { code: "PL", countryCode: "pl" },
    { code: "PT", countryCode: "pt" }, { code: "DA", countryCode: "dk" },
    { code: "FI", countryCode: "fi" }, { code: "NO", countryCode: "no" },
    { code: "CS", countryCode: "cz" }, { code: "HU", countryCode: "hu" },
    { code: "RO", countryCode: "ro" }, { code: "SK", countryCode: "sk" },
    { code: "TR", countryCode: "tr" },
];

export default function FeaturesPage() {
    return (
        <div className="pt-24 pb-16">
            <div className="container mx-auto max-w-4xl text-center mb-16">
                <Badge variant="outline" className="mb-6 rounded-full px-4 py-1.5 bg-blue-50 text-blue-600 border-blue-200">
                    Powerful Capabilities
                </Badge>
                <h1 className="text-4xl md:text-6xl font-extrabold tracking-tight text-slate-900 mb-6">
                    Everything you need to manage ETIM data
                </h1>
                <p className="text-xl text-slate-500">
                    Built from the ground up to integrate perfectly with WooCommerce. ETIM Pro offers the most robust set of tools for technical product catalogs on WordPress.
                </p>
            </div>

            <FeaturesSection />

            <div className="container mx-auto max-w-6xl mt-24">
                {/* CSV Data Mapping */}
                <div className="grid md:grid-cols-2 gap-12 items-center mb-24">
                    <div className="bg-gradient-to-br from-slate-100 to-blue-50 aspect-video rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-4 md:p-6 flex flex-col justify-center">
                        {/* CSV Preview */}
                        <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-3">
                            <div className="flex items-center gap-2 px-3 py-2 bg-slate-50 border-b border-slate-100">
                                <FileSpreadsheet className="h-4 w-4 text-emerald-600" />
                                <span className="text-xs font-medium text-slate-600">products_export.csv</span>
                                <span className="ml-auto text-[10px] text-slate-400">2,450 rows</span>
                            </div>
                            <div className="p-2">
                                <div className="grid grid-cols-3 gap-px bg-slate-100 rounded-lg overflow-hidden text-[10px]">
                                    <div className="bg-blue-50 px-2 py-1.5 font-semibold text-blue-700">SKU</div>
                                    <div className="bg-blue-50 px-2 py-1.5 font-semibold text-blue-700">ETIM Class</div>
                                    <div className="bg-blue-50 px-2 py-1.5 font-semibold text-blue-700">Value</div>
                                    <div className="bg-white px-2 py-1 text-slate-600">SKU-001</div>
                                    <div className="bg-white px-2 py-1 text-slate-600">EC001959</div>
                                    <div className="bg-white px-2 py-1 text-slate-600">60W</div>
                                    <div className="bg-slate-50 px-2 py-1 text-slate-600">SKU-002</div>
                                    <div className="bg-slate-50 px-2 py-1 text-slate-600">EC000057</div>
                                    <div className="bg-slate-50 px-2 py-1 text-slate-600">100m</div>
                                </div>
                            </div>
                        </div>
                        {/* Mapping Arrow */}
                        <div className="flex justify-center my-2">
                            <div className="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center shadow-md shadow-blue-500/30">
                                <ArrowDownToLine className="h-3.5 w-3.5 text-white" />
                            </div>
                        </div>
                        {/* Mapped Result */}
                        <div className="bg-white rounded-xl border border-emerald-200 shadow-sm overflow-hidden">
                            <div className="flex items-center gap-2 px-3 py-2 bg-emerald-50 border-b border-emerald-100">
                                <Check className="h-3.5 w-3.5 text-emerald-600" />
                                <span className="text-xs font-medium text-emerald-700">WooCommerce Mapped</span>
                                <span className="ml-auto text-[10px] text-emerald-600">2,450 synced</span>
                            </div>
                            <div className="p-2 space-y-1">
                                {[
                                    { sku: "SKU-001", name: "LED Panel 60W" },
                                    { sku: "SKU-002", name: "NYM Cable 3x1.5" },
                                ].map((item, i) => (
                                    <div key={i} className="flex items-center gap-2 px-2 py-1 text-[10px] rounded-lg bg-slate-50">
                                        <span className="font-mono text-slate-400">{item.sku}</span>
                                        <span className="text-slate-700 flex-1">{item.name}</span>
                                        <span className="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded-full font-medium text-[9px]">Mapped</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 className="text-3xl font-bold text-slate-900 mb-4">CSV Data Mapping</h3>
                        <p className="text-slate-500 text-lg mb-6">
                            Don&apos;t struggle with manual entry. Upload your suppliers&apos; CSV files and map their columns directly to ETIM classes and WooCommerce product data.
                        </p>
                        <ul className="space-y-3">
                            <li className="flex items-center gap-3 text-slate-700"><div className="w-1.5 h-1.5 rounded-full bg-blue-500" /> Visual mapping interface</li>
                            <li className="flex items-center gap-3 text-slate-700"><div className="w-1.5 h-1.5 rounded-full bg-blue-500" /> Support for massive files via chunking</li>
                            <li className="flex items-center gap-3 text-slate-700"><div className="w-1.5 h-1.5 rounded-full bg-blue-500" /> Mapping templates for repeated imports</li>
                        </ul>
                    </div>
                </div>

                {/* Multi-language Support */}
                <div className="grid md:grid-cols-2 gap-12 items-center">
                    <div className="order-2 md:order-1">
                        <h3 className="text-3xl font-bold text-slate-900 mb-4">Multi-language Support</h3>
                        <p className="text-slate-500 text-lg mb-6">
                            Operating in multiple countries? ETIM Pro pulls official ETIM translation data in up to 17 languages, ensuring your descriptions are always accurate locally.
                        </p>
                        <ul className="space-y-3">
                            <li className="flex items-center gap-3 text-slate-700"><div className="w-1.5 h-1.5 rounded-full bg-blue-500" /> Official ETIM descriptions</li>
                            <li className="flex items-center gap-3 text-slate-700"><div className="w-1.5 h-1.5 rounded-full bg-blue-500" /> Seamless Frontend Display</li>
                            <li className="flex items-center gap-3 text-slate-700"><div className="w-1.5 h-1.5 rounded-full bg-blue-500" /> Fallback language support</li>
                        </ul>
                    </div>
                    <div className="order-1 md:order-2 bg-gradient-to-br from-slate-100 to-indigo-50 aspect-video rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-4 md:p-6 flex flex-col justify-center">
                        {/* Translations preview */}
                        <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-3">
                            <div className="flex items-center gap-2 px-3 py-2 bg-slate-50 border-b border-slate-100">
                                <Globe className="h-4 w-4 text-blue-600" />
                                <span className="text-xs font-medium text-slate-600">Feature Translations</span>
                            </div>
                            <div className="p-2 space-y-1">
                                {previewLanguages.map((lang, i) => (
                                    <div key={i} className="flex items-center gap-2 px-2 py-1.5 text-[11px] rounded-lg hover:bg-slate-50">
                                        <img
                                            src={`https://flagcdn.com/w20/${lang.countryCode}.png`}
                                            alt={lang.code}
                                            width={16}
                                            height={12}
                                            className="rounded-sm"
                                            loading="lazy"
                                        />
                                        <span className="font-medium text-slate-400 w-6 text-[10px]">{lang.code}</span>
                                        <span className="text-slate-700 flex-1">{lang.text}</span>
                                        <Check className="h-3 w-3 text-emerald-500" />
                                    </div>
                                ))}
                            </div>
                        </div>
                        {/* All languages grid */}
                        <div className="flex flex-wrap gap-1.5">
                            {allLanguages.map((lang, i) => (
                                <div key={i} className="flex items-center gap-1 px-2 py-1 bg-emerald-50 rounded text-[10px] font-medium text-emerald-700 border border-emerald-100">
                                    <img
                                        src={`https://flagcdn.com/w20/${lang.countryCode}.png`}
                                        alt={lang.code}
                                        width={14}
                                        height={10}
                                        className="rounded-sm"
                                        loading="lazy"
                                    />
                                    {lang.code}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
