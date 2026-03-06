/* eslint-disable @next/next/no-img-element */
import { Badge } from "@/components/ui/badge";
import { Globe, Check, Languages, ArrowRight } from "lucide-react";

const languages = [
    { code: "EN", name: "English", countryCode: "gb", sample: "LED Panel Light" },
    { code: "DE", name: "German", countryCode: "de", sample: "LED-Panelleuchte" },
    { code: "FR", name: "French", countryCode: "fr", sample: "Panneau LED" },
    { code: "NL", name: "Dutch", countryCode: "nl", sample: "LED Paneelverlichting" },
    { code: "SV", name: "Swedish", countryCode: "se", sample: "LED-panellampa" },
    { code: "ES", name: "Spanish", countryCode: "es", sample: "Panel LED" },
    { code: "IT", name: "Italian", countryCode: "it", sample: "Pannello LED" },
    { code: "PL", name: "Polish", countryCode: "pl", sample: "Panel LED" },
    { code: "PT", name: "Portuguese", countryCode: "pt", sample: "Painel LED" },
    { code: "DA", name: "Danish", countryCode: "dk", sample: "LED-panel" },
    { code: "FI", name: "Finnish", countryCode: "fi", sample: "LED-paneeli" },
    { code: "NO", name: "Norwegian", countryCode: "no", sample: "LED-panellampe" },
    { code: "CS", name: "Czech", countryCode: "cz", sample: "LED panel" },
    { code: "HU", name: "Hungarian", countryCode: "hu", sample: "LED panel" },
    { code: "RO", name: "Romanian", countryCode: "ro", sample: "Panou LED" },
    { code: "SK", name: "Slovak", countryCode: "sk", sample: "LED panel" },
    { code: "TR", name: "Turkish", countryCode: "tr", sample: "LED Panel" },
];

export function MultiLanguageSection() {
    return (
        <section className="py-24 bg-gradient-to-b from-slate-50/50 to-white relative overflow-hidden">
            {/* Background decoration */}
            <div className="absolute bottom-0 left-0 w-[500px] h-[500px] bg-indigo-50/60 rounded-full blur-[80px] translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>

            <div className="container mx-auto px-4 md:px-6">
                <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                    {/* Left - Content */}
                    <div>
                        <Badge className="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-full px-4 py-1.5 border-0 mb-6">
                            <Languages className="h-3.5 w-3.5 mr-1.5" />
                            17 Languages
                        </Badge>
                        <h2 className="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-slate-900 mb-6 leading-tight">
                            Multi-Language Support for{" "}
                            <span className="bg-gradient-to-r from-emerald-600 to-teal-600 text-transparent bg-clip-text">Global Reach</span>
                        </h2>
                        <p className="text-lg text-slate-500 mb-8 leading-relaxed">
                            Deliver ETIM properties in up to 17 languages seamlessly. Perfect for international wholesalers and distributors managing product catalogs across European markets.
                        </p>

                        <div className="space-y-4 mb-8">
                            {[
                                { title: "Automatic Translation Mapping", desc: "ETIM standard translations are built-in — no third-party translation service needed." },
                                { title: "Per-Product Language Overrides", desc: "Customize translations for specific products when the standard doesn't fit." },
                                { title: "Bulk Language Export", desc: "Export your entire catalog in any supported language for distributor partners." },
                            ].map((item, i) => (
                                <div key={i} className="flex items-start gap-4 p-4 rounded-xl bg-slate-50 hover:bg-emerald-50/50 transition-colors group">
                                    <div className="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-emerald-200 transition-colors">
                                        <ArrowRight className="h-4 w-4 text-emerald-600" />
                                    </div>
                                    <div>
                                        <div className="font-semibold text-slate-800 mb-1">{item.title}</div>
                                        <div className="text-sm text-slate-500">{item.desc}</div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Right - Visual Illustration */}
                    <div className="relative">
                        <div className="relative bg-gradient-to-br from-slate-50 to-emerald-50/50 rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xl shadow-emerald-500/5">
                            {/* Product card being translated */}
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                                <div className="flex items-center gap-3 px-4 py-3 bg-slate-50 border-b border-slate-100">
                                    <Globe className="h-5 w-5 text-blue-600" />
                                    <span className="text-sm font-medium text-slate-700">ETIM Class: EC001959 — LED Lamps</span>
                                </div>
                                <div className="p-4">
                                    <div className="text-xs text-slate-400 uppercase tracking-wider mb-3">Feature Translations</div>
                                    <div className="space-y-2">
                                        {languages.slice(0, 6).map((lang, i) => (
                                            <div key={i} className="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50/70 transition-colors text-sm">
                                                <img
                                                    src={`https://flagcdn.com/w40/${lang.countryCode}.png`}
                                                    alt={lang.name}
                                                    width={24}
                                                    height={18}
                                                    className="rounded-sm object-cover"
                                                    loading="lazy"
                                                />
                                                <span className="font-medium text-slate-400 w-8 text-xs uppercase">{lang.code}</span>
                                                <span className="text-slate-700 flex-1">{lang.sample}</span>
                                                <Check className="h-4 w-4 text-emerald-500" />
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Language grid */}
                            <div className="bg-white rounded-2xl border border-emerald-200/50 p-4">
                                <div className="flex items-center justify-between mb-3">
                                    <span className="text-xs font-semibold text-slate-600 uppercase tracking-wider">All Supported Languages</span>
                                    <span className="text-xs text-emerald-600 font-medium">17 / 17 active</span>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {languages.map((lang, i) => (
                                        <div key={i} className="flex items-center gap-1.5 px-2.5 py-1.5 bg-emerald-50 rounded-lg text-xs font-medium text-emerald-700 border border-emerald-100">
                                            <img
                                                src={`https://flagcdn.com/w20/${lang.countryCode}.png`}
                                                alt={lang.name}
                                                width={16}
                                                height={12}
                                                className="rounded-sm object-cover"
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
            </div>
        </section>
    );
}
