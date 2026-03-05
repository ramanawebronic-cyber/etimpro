import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { Navbar } from "@/components/navbar";
import { Footer } from "@/components/footer";

const font = Inter({ subsets: ["latin"] });

export const metadata: Metadata = {
  title: "ETIM Pro | The Ultimate WooCommerce ETIM Plugin",
  description: "Manage ETIM classifications, assign groups and features, import data via CSV/XML and support multi-language ETIM for your WooCommerce store.",
  keywords: ["ETIM", "WooCommerce", "WordPress Plugin", "B2B", "Product Classification", "ETIM Import", "ETIM Features"],
  openGraph: {
    type: "website",
    locale: "en_US",
    url: "https://etim-pro.com",
    title: "ETIM Pro | WooCommerce Extension",
    description: "Manage ETIM classifications effortlessly in WooCommerce.",
    siteName: "ETIM Pro",
  },
  twitter: {
    card: "summary_large_image",
    title: "ETIM Pro | WooCommerce Extension",
    description: "Manage ETIM classifications effortlessly in WooCommerce.",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className="scroll-smooth">
      <body className={`${font.className} min-h-screen flex flex-col antialiased bg-background text-foreground`}>
        <Navbar />
        <main className="flex-1">
          {children}
        </main>
        <Footer />
      </body>
    </html>
  );
}
