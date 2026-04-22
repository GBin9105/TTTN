"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import {
  deleteCategory,
  getCategories,
  getCategoriesForSelect,
  type Category,
  type PaginatedResult,
} from "../../../services/admin/category.service";
import { getApiErrorMessage } from "../../../lib/api";

type Filters = {
  q: string;
  parent_id: string;
  status: string;
  page: number;
};

function statusText(value: unknown) {
  return value === true || value === 1 || value === "1" ? "Hiển thị" : "Ẩn";
}

export default function AdminCategoriesPage() {
  const [loading, setLoading] = useState(true);
  const [errorText, setErrorText] = useState("");
  const [rows, setRows] = useState<PaginatedResult<Category>>({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });

  const [allCategories, setAllCategories] = useState<Category[]>([]);
  const [filters, setFilters] = useState<Filters>({
    q: "",
    parent_id: "",
    status: "",
    page: 1,
  });

  async function fetchData(nextPage?: number) {
    setLoading(true);
    setErrorText("");

    try {
      const [result, categoryOptions] = await Promise.all([
        getCategories({
          q: filters.q || undefined,
          parent_id: filters.parent_id || undefined,
          status: filters.status || undefined,
          page: nextPage ?? filters.page,
          per_page: 10,
        }),
        getCategoriesForSelect(),
      ]);

      setRows(result);
      setAllCategories(categoryOptions);
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
    const ok = window.confirm("Bạn có chắc muốn xóa danh mục này?");
    if (!ok) return;

    try {
      await deleteCategory(id);
      await fetchData();
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    }
  }

  function handleSearch() {
    setFilters((prev) => ({ ...prev, page: 1 }));
    fetchData(1);
  }

  function handlePageChange(page: number) {
    setFilters((prev) => ({ ...prev, page }));
    fetchData(page);
  }

  function getParentName(parentId?: number | null) {
    if (!parentId) return "-";
    return allCategories.find((item) => item.id === parentId)?.name || "-";
  }

  return (
    <div className="space-y-6">
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">Quản lý danh mục</h1>
            <p className="mt-2 text-slate-600">
              Danh sách, tìm kiếm, lọc, phân trang và CRUD danh mục.
            </p>
          </div>

          <Link
            href="/admin/categories/create"
            className="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white"
          >
            + Thêm danh mục
          </Link>
        </div>

        {errorText ? (
          <div className="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {errorText}
          </div>
        ) : null}
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="grid gap-4 md:grid-cols-4">
          <input
            value={filters.q}
            onChange={(e) => setFilters((prev) => ({ ...prev, q: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
            placeholder="Tìm theo tên / slug"
          />

          <select
            value={filters.parent_id}
            onChange={(e) => setFilters((prev) => ({ ...prev, parent_id: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
          >
            <option value="">-- Tất cả danh mục cha --</option>
            {allCategories.map((item) => (
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

          <button
            type="button"
            onClick={handleSearch}
            className="rounded-xl bg-slate-950 px-4 py-3 text-sm font-medium text-white"
          >
            Áp dụng bộ lọc
          </button>
        </div>
      </div>

      <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead className="bg-slate-50 text-slate-600">
              <tr>
                <th className="px-5 py-3 text-left font-semibold">ID</th>
                <th className="px-5 py-3 text-left font-semibold">Tên</th>
                <th className="px-5 py-3 text-left font-semibold">Slug</th>
                <th className="px-5 py-3 text-left font-semibold">Danh mục cha</th>
                <th className="px-5 py-3 text-left font-semibold">Thứ tự</th>
                <th className="px-5 py-3 text-left font-semibold">Trạng thái</th>
                <th className="px-5 py-3 text-left font-semibold">Thao tác</th>
              </tr>
            </thead>

            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={7} className="px-5 py-10 text-center text-slate-500">
                    Đang tải dữ liệu...
                  </td>
                </tr>
              ) : rows.data.length === 0 ? (
                <tr>
                  <td colSpan={7} className="px-5 py-10 text-center text-slate-500">
                    Không có danh mục nào.
                  </td>
                </tr>
              ) : (
                rows.data.map((item) => (
                  <tr key={item.id} className="border-t border-slate-100 text-slate-700">
                    <td className="px-5 py-4">{item.id}</td>
                    <td className="px-5 py-4 font-medium text-slate-900">{item.name}</td>
                    <td className="px-5 py-4">{item.slug || "-"}</td>
                    <td className="px-5 py-4">{getParentName(item.parent_id)}</td>
                    <td className="px-5 py-4">{item.sort_order ?? 0}</td>
                    <td className="px-5 py-4">
                      <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                        {statusText(item.status)}
                      </span>
                    </td>
                    <td className="px-5 py-4">
                      <div className="flex flex-wrap gap-2">
                        <Link
                          href={`/admin/categories/${item.id}/edit`}
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
            Tổng: <span className="font-semibold text-slate-800">{rows.total}</span> danh mục
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

            <button
              type="button"
              className="rounded-lg bg-slate-950 px-3 py-2 text-sm font-medium text-white"
            >
              {rows.current_page}/{rows.last_page}
            </button>

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