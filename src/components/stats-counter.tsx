"use client";

import { useEffect, useRef, useState } from "react";
import { Star } from "lucide-react";

export function StatsCounter() {
    const ref = useRef<HTMLDivElement>(null);
    const [inView, setInView] = useState(false);
    const [products, setProducts] = useState(0);
    const [native, setNative] = useState(0);
    const [rating, setRating] = useState(0);

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setInView(true);
                    observer.disconnect();
                }
            },
            { threshold: 0.3 }
        );
        if (ref.current) observer.observe(ref.current);
        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (!inView) return;

        const duration = 2000;
        const startTime = performance.now();

        const animate = (currentTime: number) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);

            setProducts(Math.floor(eased * 50));
            setNative(Math.floor(eased * 100));
            setRating(Math.floor(eased * 49));

            if (progress < 1) requestAnimationFrame(animate);
        };

        requestAnimationFrame(animate);
    }, [inView]);

    return (
        <section className="py-6">
            <div className="container mx-auto px-4 md:px-6">
                <div
                    ref={ref}
                    className="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-4 text-center text-white shadow-xl shadow-blue-600/20"
                >
                    <div className="flex flex-col items-center">
                        <div className="text-4xl md:text-5xl font-extrabold tracking-tight">
                            {products} <span className="text-blue-200 text-3xl md:text-4xl">k+</span>
                        </div>
                        <div className="text-blue-100 mt-2 text-sm font-medium tracking-wide">
                            Products Classified
                        </div>
                    </div>

                    <div className="flex flex-col items-center border-y md:border-y-0 md:border-x border-blue-400/30 py-8 md:py-0">
                        <div className="text-4xl md:text-5xl font-extrabold tracking-tight">
                            {native} <span className="text-blue-200 text-3xl md:text-4xl">%</span>
                        </div>
                        <div className="text-blue-100 mt-2 text-sm font-medium tracking-wide">
                            WooCommerce Native
                        </div>
                    </div>

                    <div className="flex flex-col items-center">
                        <div className="text-4xl md:text-5xl font-extrabold tracking-tight flex items-center justify-center gap-2">
                            {(rating / 10).toFixed(1)} <Star className="h-7 w-7 md:h-8 md:w-8 fill-yellow-300 text-yellow-300" />
                        </div>
                        <div className="text-blue-100 mt-2 text-sm font-medium tracking-wide">
                            User Rating
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
