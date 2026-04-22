import Link from "next/link";
import type { ReactNode } from "react";

const menuItems = [
  { label: "Dashboard", href: "/admin/dashboard" },
  { label: "Sản phẩm", href: "/admin/products" },
  { label: "Danh mục", href: "/admin/categories" },
  { label: "Thương hiệu", href: "/admin/brands" },
  { label: "Thuộc tính", href: "/admin/attributes" },
  { label: "Đơn hàng", href: "/admin/orders" },
  { label: "Phiếu nhập kho", href: "/admin/stock-receipts" },
  { label: "Log tồn kho", href: "/admin/inventory-logs" },
  { label: "Khuyến mãi", href: "/admin/promotions" },
  { label: "Bài viết", href: "/admin/posts" },
  { label: "Chủ đề bài viết", href: "/admin/post-topics" },
  { label: "Liên hệ", href: "/admin/contacts" },
  { label: "Banner", href: "/admin/banners" },
  { label: "Thành viên", href: "/admin/members" },
  { label: "Cài đặt", href: "/admin/settings" },
];

export default function AdminLayout({
  children,
}: {
  children: ReactNode;
}) {
  return (
    <div className="min-h-screen bg-slate-100 text-slate-900">
      <div className="flex min-h-screen">
        <aside className="hidden w-72 shrink-0 border-r border-slate-200 bg-slate-950 text-white lg:flex lg:flex-col">
          <div className="border-b border-white/10 px-6 py-5">
            <p className="text-xs uppercase tracking-[0.2em] text-slate-400">
              Power Tools
            </p>
            <h1 className="mt-2 text-2xl font-bold">Admin Panel</h1>
          </div>

          <nav className="flex-1 space-y-1 px-3 py-4">
            {menuItems.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className="block rounded-xl px-4 py-3 text-sm text-slate-200 transition hover:bg-white/10 hover:text-white"
              >
                {item.label}
              </Link>
            ))}
          </nav>


        </aside>

        <div className="flex min-w-0 flex-1 flex-col">
          <header className="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div className="flex items-center justify-between px-4 py-4 md:px-6">
              <div>
                <p className="text-sm text-slate-500">Hệ thống quản trị</p>
                <h2 className="text-xl font-bold">Power Tools Dashboard</h2>
              </div>

              <div className="flex items-center gap-3">
                <div className="hidden rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600 md:block">
                  Admin mode
                </div>
                <div className="flex h-11 w-11 items-center justify-center rounded-full bg-slate-950 text-sm font-semibold text-white">
                  A
                </div>
              </div>
            </div>
          </header>

          <main className="flex-1 p-4 md:p-6">{children}</main>
        </div>
      </div>
    </div>
  );
}