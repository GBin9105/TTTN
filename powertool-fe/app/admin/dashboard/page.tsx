"use client";

import { useEffect, useMemo, useState } from "react";
import { getAdminDashboard, type AdminDashboardStats } from "../../../services/admin/dashboard.service";

type DashboardState = {
  stats: AdminDashboardStats | null;
  loading: boolean;
  error: string;
};

const mockStats: AdminDashboardStats = {
  products_count: 342,
  categories_count: 28,
  brands_count: 17,
  posts_count: 65,
  users_count: 5621,
  orders_count: 1284,
  contacts_count: 46,
  banners_count: 8,
  promotions_count: 12,
};

const recentOrders = [
  {
    code: "ORD-240423-001",
    customer: "Nguyễn Văn A",
    amount: "3.250.000đ",
    status: "Chờ xác nhận",
  },
  {
    code: "ORD-240423-002",
    customer: "Trần Thị B",
    amount: "1.890.000đ",
    status: "Đang giao",
  },
  {
    code: "ORD-240423-003",
    customer: "Lê Văn C",
    amount: "5.420.000đ",
    status: "Hoàn thành",
  },
  {
    code: "ORD-240423-004",
    customer: "Phạm Thị D",
    amount: "980.000đ",
    status: "Đã hủy",
  },
];

const lowStockProducts = [
  { name: "Máy khoan pin Bosch GSB 120-LI", stock: 3 },
  { name: "Máy mài góc Makita GA4030", stock: 5 },
  { name: "Bộ tua vít 32 món Stanley", stock: 2 },
  { name: "Máy cưa lọng Dewalt DW341K", stock: 4 },
];

function getStatusClass(status: string) {
  switch (status) {
    case "Hoàn thành":
      return "bg-emerald-100 text-emerald-700";
    case "Đang giao":
      return "bg-blue-100 text-blue-700";
    case "Chờ xác nhận":
      return "bg-amber-100 text-amber-700";
    case "Đã hủy":
      return "bg-rose-100 text-rose-700";
    default:
      return "bg-slate-100 text-slate-700";
  }
}

export default function AdminDashboardPage() {
  const [state, setState] = useState<DashboardState>({
    stats: null,
    loading: true,
    error: "",
  });

  useEffect(() => {
    let isMounted = true;

    async function fetchDashboard() {
      try {
        const result = await getAdminDashboard();

        const stats =
          result?.data && typeof result.data === "object"
            ? (result.data as AdminDashboardStats)
            : mockStats;

        if (!isMounted) return;

        setState({
          stats,
          loading: false,
          error: "",
        });
      } catch {
        if (!isMounted) return;

        setState({
          stats: mockStats,
          loading: false,
          error: " Đang hiển thị dữ liệu seeder.",
        });
      }
    }

    fetchDashboard();

    return () => {
      isMounted = false;
    };
  }, []);

  const cards = useMemo(() => {
    const stats = state.stats ?? mockStats;

    return [
      {
        title: "Tổng đơn hàng",
        value: stats.orders_count ?? 0,
        change: "+12.4%",
        note: "so với tháng trước",
      },
      {
        title: "Sản phẩm",
        value: stats.products_count ?? 0,
        change: "+16",
        note: "sản phẩm đang hoạt động",
      },
      {
        title: "Danh mục",
        value: stats.categories_count ?? 0,
        change: "+4",
        note: "danh mục hiện có",
      },
      {
        title: "Thành viên",
        value: stats.users_count ?? 0,
        change: "+234",
        note: "người dùng mới",
      },
    ];
  }, [state.stats]);

  return (
    <div className="space-y-6">
      <section className="rounded-3xl bg-slate-950 p-6 text-white shadow-lg md:p-8">
        <div className="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
          <div>
            <p className="text-sm text-slate-300">Tổng quan hệ thống</p>
            <h1 className="mt-2 text-3xl font-bold md:text-4xl">
              Dashboard quản trị Power Tools
            </h1>
            <p className="mt-4 max-w-2xl text-sm leading-7 text-slate-300 md:text-base">
              Đây là giao diện dashboard admin đầu tiên. Trước mắt ta khóa bố cục,
              sau đó mới nối dần products, categories, orders và các module còn lại.
            </p>
          </div>

          <div className="rounded-2xl bg-white/10 p-5">
            <p className="text-sm text-slate-300">Trạng thái hôm nay</p>
            <div className="mt-4 space-y-3 text-sm">
              <div className="flex items-center justify-between">
                <span className="text-slate-300">Đơn mới</span>
                <span className="font-semibold text-white">28</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-slate-300">Liên hệ mới</span>
                <span className="font-semibold text-white">6</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-slate-300">Sắp hết hàng</span>
                <span className="font-semibold text-white">14</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {state.error ? (
        <div className="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
          {state.error}
        </div>
      ) : null}

      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {cards.map((item) => (
          <div
            key={item.title}
            className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
          >
            <p className="text-sm text-slate-500">{item.title}</p>
            <div className="mt-3 flex items-end justify-between gap-3">
              <h3 className="text-2xl font-bold text-slate-900">
                {state.loading ? "..." : item.value}
              </h3>
              <span className="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                {item.change}
              </span>
            </div>
            <p className="mt-2 text-sm text-slate-500">{item.note}</p>
          </div>
        ))}
      </section>

      <section className="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div className="border-b border-slate-200 px-5 py-4">
            <h2 className="text-lg font-semibold text-slate-900">
              Đơn hàng gần đây
            </h2>
          </div>

          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead className="bg-slate-50 text-slate-600">
                <tr>
                  <th className="px-5 py-3 text-left font-semibold">Mã đơn</th>
                  <th className="px-5 py-3 text-left font-semibold">Khách hàng</th>
                  <th className="px-5 py-3 text-left font-semibold">Tổng tiền</th>
                  <th className="px-5 py-3 text-left font-semibold">Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                {recentOrders.map((order) => (
                  <tr
                    key={order.code}
                    className="border-t border-slate-100 text-slate-700"
                  >
                    <td className="px-5 py-4 font-medium">{order.code}</td>
                    <td className="px-5 py-4">{order.customer}</td>
                    <td className="px-5 py-4">{order.amount}</td>
                    <td className="px-5 py-4">
                      <span
                        className={`rounded-full px-2.5 py-1 text-xs font-semibold ${getStatusClass(
                          order.status
                        )}`}
                      >
                        {order.status}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <div className="space-y-6">
          <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-900">
              Module từ API
            </h2>
            <div className="mt-4 grid gap-3">
              <a
                href="/admin/products"
                className="rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
              >
                Quản lý sản phẩm
              </a>
              <a
                href="/admin/categories"
                className="rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
              >
                Quản lý danh mục
              </a>
              <a
                href="/admin/orders"
                className="rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
              >
                Quản lý đơn hàng
              </a>
              <a
                href="/admin/settings"
                className="rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
              >
                Cài đặt website
              </a>
            </div>
          </div>

          <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-900">
              Sản phẩm sắp hết hàng
            </h2>
            <div className="mt-4 space-y-4">
              {lowStockProducts.map((item) => (
                <div
                  key={item.name}
                  className="flex items-start justify-between gap-4"
                >
                  <div>
                    <p className="text-sm font-medium text-slate-800">
                      {item.name}
                    </p>
                    <p className="mt-1 text-xs text-slate-500">
                      Cần bổ sung nhập kho
                    </p>
                  </div>
                  <span className="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">
                    {item.stock} còn lại
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section className="grid gap-6 lg:grid-cols-2">
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 className="text-lg font-semibold text-slate-900">
            Doanh thu 7 ngày gần đây
          </h2>

          <div className="mt-6 flex h-64 items-end gap-3 rounded-2xl bg-slate-50 p-4">
            {[35, 60, 48, 72, 55, 80, 68].map((value, index) => (
              <div key={index} className="flex flex-1 flex-col items-center gap-2">
                <div
                  className="w-full rounded-t-xl bg-slate-900"
                  style={{ height: `${value * 2}px` }}
                />
                <span className="text-xs text-slate-500">T{index + 1}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 className="text-lg font-semibold text-slate-900">
            Ghi chú triển khai
          </h2>

          <div className="mt-4 space-y-4 text-sm leading-7 text-slate-600">
            <p>
              Route admin dashboard của bạn là
              <span className="font-medium text-slate-900">
                {" "}
                GET /api/v1/admin/dashboard
              </span>
              .
            </p>
            <p>
              Hiện tại page này sẽ cố gọi dữ liệu thật. Nếu API chưa đăng nhập
              được, nó sẽ rơi về dữ liệu demo để bạn tiếp tục dựng UI.
            </p>
            <p>
              Bước tiếp theo nên làm:
              <span className="font-medium text-slate-900">
                {" "}
                products list → categories list → orders list
              </span>
              .
            </p>
          </div>
        </div>
      </section>
    </div>
  );
}