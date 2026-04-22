"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import {
  deleteAttribute,
  getAttributes,
  type Attribute,
  type PaginatedResult,
} from "../../../services/admin/attribute.service";
import { getApiErrorMessage } from "../../../lib/api";

type Filters = {
  q: string;
  page: number;
};

function statusText(value: unknown) {
  return value === true || value === 1 || value === "1" ? "Hiển thị" : "Ẩn";
}

export default function AdminAttributesPage() {
  const [loading, setLoading] = useState(true);
  const [errorText, setErrorText] = useState("");
  const [rows, setRows] = useState<PaginatedResult<Attribute>>({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });

  const [filters, setFilters] = useState<Filters>({
    q: "",
    page: 1,
  });

  async function fetchData(nextPage?: number) {
    setLoading(true);
    setErrorText("");

    try {
      const result = await getAttributes({
        q: filters.q || undefined,
        page: nextPage ?? filters.page,
        per_page: 10,
      });

      setRows(result);
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
    const ok = window.confirm("Bạn có chắc muốn xóa thuộc tính này?");
    if (!ok) return;

    try {
      await deleteAttribute(id);
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

  return (
    <div className="space-y-6">
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">Quản lý thuộc tính</h1>
            <p className="mt-2 text-slate-600">
              Danh sách, tìm kiếm, phân trang và CRUD thuộc tính.
            </p>
          </div>

          <Link
            href="/admin/attributes/create"
            className="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white"
          >
            + Thêm thuộc tính
          </Link>
        </div>

        {errorText ? (
          <div className="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {errorText}
          </div>
        ) : null}
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="grid gap-4 md:grid-cols-[1fr_180px]">
          <input
            value={filters.q}
            onChange={(e) => setFilters((prev) => ({ ...prev, q: e.target.value }))}
            className="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
            placeholder="Tìm theo tên / slug"
          />

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
                <th className="px-5 py-3 text-left font-semibold">Loại</th>
                <th className="px-5 py-3 text-left font-semibold">Giá trị</th>
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
                    Không có thuộc tính nào.
                  </td>
                </tr>
              ) : (
                rows.data.map((item) => (
                  <tr key={item.id} className="border-t border-slate-100 text-slate-700">
                    <td className="px-5 py-4">{item.id}</td>
                    <td className="px-5 py-4 font-medium text-slate-900">{item.name}</td>
                    <td className="px-5 py-4">{item.slug || "-"}</td>
                    <td className="px-5 py-4">{item.type || "-"}</td>
                    <td className="px-5 py-4">
                      <div className="flex max-w-[280px] flex-wrap gap-2">
                        {Array.isArray(item.values) && item.values.length > 0 ? (
                          item.values.map((value, index) => (
                            <span
                              key={`${item.id}-${index}`}
                              className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700"
                            >
                              {value}
                            </span>
                          ))
                        ) : (
                          <span className="text-slate-400">-</span>
                        )}
                      </div>
                    </td>
                    <td className="px-5 py-4">
                      <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                        {statusText(item.status)}
                      </span>
                    </td>
                    <td className="px-5 py-4">
                      <div className="flex flex-wrap gap-2">
                        <Link
                          href={`/admin/attributes/${item.id}/edit`}
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
            Tổng: <span className="font-semibold text-slate-800">{rows.total}</span> thuộc tính
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