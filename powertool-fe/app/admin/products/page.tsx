"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import {
  deleteProduct,
  getBrandsForSelect,
  getCategoriesForSelect,
  getProducts,
  type PaginatedResult,
  type Product,
  type ProductOption,
} from "../../../services/admin/product.service";
import { getApiErrorMessage } from "../../../lib/api";

type Filters = {
  q: string;
  brand_id: string;
  category_id: string;
  status: string;
  is_featured: string;
  is_new: string;
  page: number;
};

function statusText(value: unknown) {
  return value === true || value === 1 || value === "1" ? "Hiển thị" : "Ẩn";
}

export default function AdminProductsPage() {
  const [loading, setLoading] = useState(true);
  const [errorText, setErrorText] = useState("");
  const [rows, setRows] = useState<PaginatedResult<Product>>({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });

  const [brands, setBrands] = useState<ProductOption[]>([]);
  const [categories, setCategories] = useState<ProductOption[]>([]);
  const [filters, setFilters] = useState<Filters>({
    q: "",
    brand_id: "",
    category_id: "",
    status: "",
    is_featured: "",
    is_new: "",
    page: 1,
  });

  async function fetchData(nextPage?: number) {
    setLoading(true);
    setErrorText("");

    try {
      const [productResult, brandRows, categoryRows] = await Promise.all([
        getProducts({
          q: filters.q || undefined,
          brand_id: filters.brand_id || undefined,
          category_id: filters.category_id || undefined,
          status: filters.status || undefined,
          is_featured: filters.is_featured || undefined,
          is_new: filters.is_new || undefined,
          page: nextPage ?? filters.page,
          per_page: 10,
        }),
        getBrandsForSelect(),
        getCategoriesForSelect(),
      ]);

      setRows(productResult);
      setBrands(brandRows);
      setCategories(categoryRows);
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    fetchData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  async function handleDelete(id: number) {
    const ok = window.confirm("Bạn có chắc muốn xóa sản phẩm này?");
    if (!ok) return;

    try {
      await deleteProduct(id);
      await fetchData();
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    }
  }

  function handleSearch() {
    const nextFilters = { ...filters, page: 1 };
    setFilters(nextFilters);
    fetchData(1);
  }

  function handlePageChange(page: number) {
    setFilters((prev) => ({ ...prev, page }));
    fetchData(page);
  }

  return (
    <div className="space-y-6">
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">Quản lý sản phẩm</h1>
            <p className="mt-2 text-slate-600">
              Danh sách, lọc, tìm kiếm, phân trang và CRUD sản phẩm.
            </p>
          </div>

          <Link
            href="/admin/products/create"
            className="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white"
          >
            + Thêm sản phẩm
          </Link>
        </div>

        {errorText ? (
          <div className="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {errorText}
          </div>
        ) : null}
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <input
            value={filters.q}
            onChange={(e) => setFilters((prev) => ({ ...prev, q: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
            placeholder="Tìm theo tên / slug / SKU"
          />

          <select
            value={filters.brand_id}
            onChange={(e) => setFilters((prev) => ({ ...prev, brand_id: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
          >
            <option value="">-- Tất cả thương hiệu --</option>
            {brands.map((item) => (
              <option key={item.id} value={item.id}>
                {item.name}
              </option>
            ))}
          </select>

          <select
            value={filters.category_id}
            onChange={(e) => setFilters((prev) => ({ ...prev, category_id: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
          >
            <option value="">-- Tất cả danh mục --</option>
            {categories.map((item) => (
              <option key={item.id} value={item.id}>
                {item.name}
              </option>
            ))}
          </select>

          <select
            value={filters.status}
            onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
          >
            <option value="">-- Trạng thái --</option>
            <option value="1">Hiển thị</option>
            <option value="0">Ẩn</option>
          </select>

          <select
            value={filters.is_featured}
            onChange={(e) => setFilters((prev) => ({ ...prev, is_featured: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
          >
            <option value="">-- Nổi bật --</option>
            <option value="1">Có</option>
            <option value="0">Không</option>
          </select>

          <select
            value={filters.is_new}
            onChange={(e) => setFilters((prev) => ({ ...prev, is_new: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
          >
            <option value="">-- Sản phẩm mới --</option>
            <option value="1">Có</option>
            <option value="0">Không</option>
          </select>
        </div>

        <div className="mt-4 flex flex-wrap gap-3">
          <button
            type="button"
            onClick={handleSearch}
            className="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white"
          >
            Áp dụng bộ lọc
          </button>

          <button
            type="button"
            onClick={() => {
              const reset = {
                q: "",
                brand_id: "",
                category_id: "",
                status: "",
                is_featured: "",
                is_new: "",
                page: 1,
              };
              setFilters(reset);
              setTimeout(() => fetchData(1), 0);
            }}
            className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
          >
            Xóa bộ lọc
          </button>
        </div>
      </div>

      <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead className="bg-slate-50 text-slate-600">
              <tr>
                <th className="px-5 py-3 text-left font-semibold">ID</th>
                <th className="px-5 py-3 text-left font-semibold">Sản phẩm</th>
                <th className="px-5 py-3 text-left font-semibold">Danh mục</th>
                <th className="px-5 py-3 text-left font-semibold">Thương hiệu</th>
                <th className="px-5 py-3 text-left font-semibold">Giá</th>
                <th className="px-5 py-3 text-left font-semibold">Tồn kho</th>
                <th className="px-5 py-3 text-left font-semibold">Trạng thái</th>
                <th className="px-5 py-3 text-left font-semibold">Thao tác</th>
              </tr>
            </thead>

            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={8} className="px-5 py-10 text-center text-slate-500">
                    Đang tải dữ liệu...
                  </td>
                </tr>
              ) : rows.data.length === 0 ? (
                <tr>
                  <td colSpan={8} className="px-5 py-10 text-center text-slate-500">
                    Không có sản phẩm nào.
                  </td>
                </tr>
              ) : (
                rows.data.map((item) => (
                  <tr key={item.id} className="border-t border-slate-100 text-slate-700">
                    <td className="px-5 py-4">{item.id}</td>
                    <td className="px-5 py-4">
                      <div>
                        <p className="font-medium text-slate-900">{item.name}</p>
                        <p className="mt-1 text-xs text-slate-500">
                          SKU: {item.sku || "-"} | Slug: {item.slug || "-"}
                        </p>
                      </div>
                    </td>
                    <td className="px-5 py-4">{item.category?.name || "-"}</td>
                    <td className="px-5 py-4">{item.brand?.name || "-"}</td>
                    <td className="px-5 py-4">
                      {Number(item.price ?? 0).toLocaleString("vi-VN")}đ
                    </td>
                    <td className="px-5 py-4">{item.stock_quantity ?? 0}</td>
                    <td className="px-5 py-4">
                      <div className="flex flex-wrap gap-2">
                        <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                          {statusText(item.status)}
                        </span>

                        {item.is_featured ? (
                          <span className="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                            Nổi bật
                          </span>
                        ) : null}

                        {item.is_new ? (
                          <span className="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                            Mới
                          </span>
                        ) : null}
                      </div>
                    </td>
                    <td className="px-5 py-4">
                      <div className="flex flex-wrap gap-2">
                        <Link
                          href={`/admin/products/${item.id}/edit`}
                          className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700"
                        >
                          Sửa
                        </Link>
                        <button
                          type="button"
                          onClick={() => handleDelete(item.id)}
                          className="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700"
                        >
                          Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
          <p className="text-sm text-slate-500">
            Tổng: <span className="font-semibold text-slate-800">{rows.total}</span> sản phẩm
          </p>

          <div className="flex flex-wrap gap-2">
            <button
              type="button"
              disabled={rows.current_page <= 1}
              onClick={() => handlePageChange(rows.current_page - 1)}
              className="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 disabled:opacity-50"
            >
              Trước
            </button>

            {Array.from({ length: rows.last_page }, (_, i) => i + 1)
              .slice(Math.max(rows.current_page - 3, 0), Math.max(rows.current_page + 2, 0))
              .map((page) => (
                <button
                  key={page}
                  type="button"
                  onClick={() => handlePageChange(page)}
                  className={
                    page === rows.current_page
                      ? "rounded-lg bg-slate-950 px-3 py-2 text-sm font-medium text-white"
                      : "rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700"
                  }
                >
                  {page}
                </button>
              ))}

            <button
              type="button"
              disabled={rows.current_page >= rows.last_page}
              onClick={() => handlePageChange(rows.current_page + 1)}
              className="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 disabled:opacity-50"
            >
              Sau
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}