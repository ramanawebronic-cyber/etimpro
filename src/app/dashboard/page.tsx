"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { CheckCircle2, ArrowRight } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function DashboardPage() {
    const [userName, setUserName] = useState("User");

    useEffect(() => {
        const token = localStorage.getItem("etim_token");
        if (!token) {
            window.location.href = "/login";
        } else {
            setUserName(localStorage.getItem("etim_user_display_name") || "User");
        }
    }, []);

    return (
        <div className="min-h-[calc(100vh-64px)] flex items-center justify-center bg-slate-50">
            <div className="bg-white p-12 rounded-3xl shadow-xl border border-slate-100 text-center max-w-lg w-full">
                <div className="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6 text-emerald-600">
                    <CheckCircle2 className="w-10 h-10" />
                </div>
                <h1 className="text-3xl font-bold text-slate-900 mb-4">
                    Login Successful – Welcome {userName}
                </h1>
                <p className="text-slate-500 mb-8">
                    You have successfully authenticated with the WordPress backend.
                </p>
                <Button asChild className="rounded-full px-8 py-6 bg-blue-600 hover:bg-blue-700 text-lg font-bold">
                    <Link href="/my-account">
                        Go to My Account <ArrowRight className="ml-2 w-5 h-5" />
                    </Link>
                </Button>
            </div>
        </div>
    );
}
