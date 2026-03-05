import { Badge } from "@/components/ui/badge";

export function DashboardPreview() {
    return (
        <section className="py-24 relative overflow-hidden bg-background">
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] bg-primary/20 blur-[120px] rounded-full pointer-events-none opacity-50 z-0"></div>

            <div className="container mx-auto relative z-10 px-4 md:px-6">
                <div className="flex flex-col items-center justify-center space-y-4 text-center mb-16">
                    <Badge className="bg-primary/20 text-primary hover:bg-primary/30 rounded-full px-4 py-1.5 transition-colors border-0">
                        Native Deep Integration
                    </Badge>
                    <div className="space-y-2 max-w-3xl">
                        <h2 className="text-3xl font-bold tracking-tighter sm:text-5xl md:text-6xl text-foreground">
                            Powerful WooCommerce&apos;s Native Experience
                        </h2>
                        <p className="max-w-[700px] text-muted-foreground md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed mx-auto mt-4 px-2">
                            Forget clunky external sync tools. Native integration means instant ETIM classifications without leaving wp-admin.
                        </p>
                    </div>
                </div>

                <div className="relative mx-auto mt-12 max-w-[1000px] aspect-[16/9] w-full rounded-2xl md:rounded-[3rem] p-2 md:p-4 bg-muted/40 backdrop-blur border border-primary/20 shadow-2xl dark:shadow-primary/5">
                    <div className="rounded-xl overflow-hidden shadow-sm h-full w-full bg-background border flex flex-col">
                        {/* Fake Browser Header */}
                        <div className="h-12 w-full bg-muted border-b flex items-center px-4 gap-2">
                            <div className="flex gap-1.5">
                                <div className="w-3 h-3 rounded-full bg-red-400"></div>
                                <div className="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div className="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div className="mx-auto bg-background rounded-md px-32 py-1 text-xs text-muted-foreground border shadow-sm">
                                yoursite.com/wp-admin/admin.php?page=etim-pro
                            </div>
                        </div>

                        {/* Fake Dashboard Body */}
                        <div className="flex-1 flex overflow-hidden">
                            {/* Sidebar */}
                            <div className="w-64 border-r bg-muted/30 p-4 hidden md:flex flex-col gap-2">
                                <div className="px-3 py-2 text-sm font-semibold mb-2">WooCommerce</div>
                                <div className="px-3 py-2 rounded-md bg-transparent text-sm hover:bg-muted text-muted-foreground transition-colors cursor-pointer">Dashboard</div>
                                <div className="px-3 py-2 rounded-md bg-transparent text-sm hover:bg-muted text-muted-foreground transition-colors cursor-pointer">Products</div>
                                <div className="px-3 py-2 rounded-md bg-primary/10 text-primary text-sm font-medium cursor-pointer">ETIM Pro Manager</div>
                                <div className="px-3 py-2 rounded-md bg-transparent text-sm hover:bg-muted text-muted-foreground transition-colors cursor-pointer">Settings</div>
                            </div>

                            {/* Main Content Area */}
                            <div className="flex-1 p-6 md:p-8 overflow-y-auto bg-background">
                                <div className="flex justify-between items-center mb-6">
                                    <h3 className="text-2xl font-semibold">ETIM Configuration</h3>
                                    <button className="px-4 py-2 bg-primary text-primary-foreground text-sm rounded-md font-medium shadow-sm hover:opacity-90">
                                        Import Features
                                    </button>
                                </div>

                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                                    <div className="border rounded-xl p-4 bg-muted/10">
                                        <div className="text-sm text-muted-foreground mb-1">Total Products</div>
                                        <div className="text-3xl font-bold">14,392</div>
                                    </div>
                                    <div className="border rounded-xl p-4 bg-primary/5 border-primary/20">
                                        <div className="text-sm text-primary mb-1">ETIM Assigned</div>
                                        <div className="text-3xl font-bold text-primary">8,204</div>
                                    </div>
                                    <div className="border rounded-xl p-4 bg-muted/10">
                                        <div className="text-sm text-muted-foreground mb-1">Active Classes</div>
                                        <div className="text-3xl font-bold">142</div>
                                    </div>
                                </div>

                                <div className="border rounded-xl overflow-hidden bg-background">
                                    <div className="grid grid-cols-5 p-4 border-b bg-muted/40 text-sm font-medium">
                                        <div className="col-span-2">Class Name</div>
                                        <div>Code</div>
                                        <div>Products Linked</div>
                                        <div>Status</div>
                                    </div>
                                    {[
                                        { name: 'Lighting - LED Lamps', code: 'EC001959', products: 1240, status: 'Active' },
                                        { name: 'Cables - power cables', code: 'EC000057', products: 830, status: 'Active' },
                                        { name: 'Switches - Installation switches', code: 'EC001590', products: 450, status: 'Syncing' },
                                        { name: 'Tools - Pliers', code: 'EC000836', products: 320, status: 'Active' },
                                    ].map((row, i) => (
                                        <div key={i} className="grid grid-cols-5 p-4 border-b last:border-0 text-sm hover:bg-muted/30 transition-colors">
                                            <div className="col-span-2 font-medium">{row.name}</div>
                                            <div className="text-muted-foreground">{row.code}</div>
                                            <div>{row.products}</div>
                                            <div>
                                                <span className={`px-2 py-1 rounded text-xs ${row.status === 'Active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'}`}>
                                                    {row.status}
                                                </span>
                                            </div>
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
