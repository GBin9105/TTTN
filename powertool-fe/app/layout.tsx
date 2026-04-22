import "./globals.css";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Power Tools FE",
  description: "Frontend for Laravel Power Tools API",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="vi">
      <body>{children}</body>
    </html>
  );
}