import { Badge } from "@/components/ui/badge";
import { FileSpreadsheet, ArrowRight, Check, Upload, Table2, ArrowDownToLine } from "lucide-react";

export function CsvDataMappingSection() {
    return (
        <section className="py-24 bg-white relative overflow-hidden">
            {/* Background decoration */}
            <div className="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-50/60 rounded-full blur-[80px] -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>

            <div className="container mx-auto px-4 md:px-6">
                <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                    {/* Left - Visual Illustration */}
                    <div className="relative order-2 lg:order-1">
                        <div className="relative bg-gradient-to-br from-slate-50 to-blue-50/50 rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-xl shadow-blue-500/5">
                            {/* CSV File Preview */}
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                                <div className="flex items-center gap-3 px-4 py-3 bg-slate-50 border-b border-slate-100">
                                    <FileSpreadsheet className="h-5 w-5 text-emerald-600" />
                                    <span className="text-sm font-medium text-slate-700">etim_products_export.csv</span>
                                    <span className="ml-auto text-xs text-slate-400">2,450 rows</span>
                                </div>
                                <div className="p-3 overflow-hidden">
                                    <div className="grid grid-cols-4 gap-px bg-slate-100 rounded-lg overflow-hidden text-xs">
                                        <div className="bg-blue-50 px-3 py-2 font-semibold text-blue-700">Product SKU</div>
                                        <div className="bg-blue-50 px-3 py-2 font-semibold text-blue-700">ETIM Class</div>
                                        <div className="bg-blue-50 px-3 py-2 font-semibold text-blue-700">Feature</div>
                                        <div className="bg-blue-50 px-3 py-2 font-semibold text-blue-700">Value</div>

                                        <div className="bg-white px-3 py-2 text-slate-600">SKU-001</div>
                                        <div className="bg-white px-3 py-2 text-slate-600">EC001959</div>
                                        <div className="bg-white px-3 py-2 text-slate-600">Wattage</div>
                                        <div className="bg-white px-3 py-2 text-slate-600">60W</div>

                                        <div className="bg-slate-50 px-3 py-2 text-slate-600">SKU-002</div>
                                        <div className="bg-slate-50 px-3 py-2 text-slate-600">EC000057</div>
                                        <div className="bg-slate-50 px-3 py-2 text-slate-600">Length</div>
                                        <div className="bg-slate-50 px-3 py-2 text-slate-600">100m</div>

                                        <div className="bg-white px-3 py-2 text-slate-600">SKU-003</div>
                                        <div className="bg-white px-3 py-2 text-slate-600">EC001590</div>
                                        <div className="bg-white px-3 py-2 text-slate-600">Voltage</div>
                                        <div className="bg-white px-3 py-2 text-slate-600">230V</div>
                                    </div>
                                </div>
                            </div>

                            {/* Mapping Flow Arrow */}
                            <div className="flex items-center justify-center gap-4 mb-6">
                                <div className="h-px flex-1 bg-gradient-to-r from-transparent via-blue-300 to-transparent"></div>
                                <div className="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                                    <ArrowDownToLine className="h-5 w-5 text-white" />
                                </div>
                                <div className="h-px flex-1 bg-gradient-to-r from-transparent via-blue-300 to-transparent"></div>
                            </div>

                            {/* Mapped Result */}
                            <div className="bg-white rounded-2xl border border-emerald-200 shadow-sm overflow-hidden">
                                <div className="flex items-center gap-3 px-4 py-3 bg-emerald-50 border-b border-emerald-100">
                                    <Table2 className="h-5 w-5 text-emerald-600" />
                                    <span className="text-sm font-medium text-emerald-700">WooCommerce Products — Mapped</span>
                                    <div className="ml-auto flex items-center gap-1 text-xs text-emerald-600">
                                        <Check className="h-3 w-3" /> 2,450 synced
                                    </div>
                                </div>
                                <div className="p-4 space-y-2">
                                    {[
                                        { sku: "SKU-001", name: "LED Panel 60W", cls: "Lighting - LED", status: "Mapped" },
                                        { sku: "SKU-002", name: "NYM Cable 3x1.5", cls: "Power Cables", status: "Mapped" },
                                        { sku: "SKU-003", name: "Smart Switch Pro", cls: "Inst. Switches", status: "Mapped" },
                                    ].map((item, i) => (
                                        <div key={i} className="flex items-center gap-3 px-3 py-2.5 bg-slate-50 rounded-xl text-xs">
                                            <span className="font-mono text-slate-400 w-16">{item.sku}</span>
                                            <span className="font-medium text-slate-700 flex-1">{item.name}</span>
                                            <span className="text-slate-500 hidden sm:block">{item.cls}</span>
                                            <span className="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full font-medium">{item.status}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right - Content */}
                    <div className="order-1 lg:order-2">
                        <Badge className="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-full px-4 py-1.5 border-0 mb-6">
                            <Upload className="h-3.5 w-3.5 mr-1.5" />
                            Bulk Import
                        </Badge>
                        <h2 className="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-slate-900 mb-6 leading-tight">
                            CSV/XML Data Mapping{" "}
                            <span className="bg-gradient-to-r from-blue-600 to-indigo-600 text-transparent bg-clip-text">Made Simple</span>
                        </h2>
                        <p className="text-lg text-slate-500 mb-8 leading-relaxed">
                            Upload your ETIM data files and let ETIM Pro automatically map columns to WooCommerce product attributes. No manual entry, no errors — just seamless bulk classification.
                        </p>

                        <div className="space-y-4">
                            {[
                                { title: "Smart Column Detection", desc: "Auto-detects ETIM class codes, feature codes, and values from your CSV/XML headers." },
                                { title: "Batch Processing", desc: "Process thousands of products in a single import with progress tracking." },
                                { title: "Validation & Preview", desc: "Preview mapped data before applying — catch errors before they reach your store." },
                            ].map((item, i) => (
                                <div key={i} className="flex items-start gap-4 p-4 rounded-xl bg-slate-50 hover:bg-blue-50/50 transition-colors group">
                                    <div className="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-blue-200 transition-colors">
                                        <ArrowRight className="h-4 w-4 text-blue-600" />
                                    </div>
                                    <div>
                                        <div className="font-semibold text-slate-800 mb-1">{item.title}</div>
                                        <div className="text-sm text-slate-500">{item.desc}</div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
