"use client";

import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import {
  createBrand,
  getBrand,
  updateBrand,
  type Brand,
  type BrandFormPayload,
} from "../../../../services/admin/brand.service";
import { getApiErrorMessage } from "../../../../lib/api";

type BrandFormProps = {
  mode: "create" | "edit";
  brandId?: string;
};

const defaultForm: BrandFormPayload = {
  name: "",
  slug: "",
  description: "",
  logo: "",
  status: true,
};

function toBool(value: unknown) {
  return value === true || value === 1 || value === "1";
}

function toSlug(value: string) {
  return value
    .toLowerCase()
    .trim()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/đ/g, "d")
    .replace(/[^a-z0-9\s-]/g, "")
    .replace(/\s+/g, "-")
    .replace(/-+/g, "-");
}

export default function BrandForm({ mode, brandId }: BrandFormProps) {
  const router = useRouter();

  const [loading, setLoading] = useState(mode === "edit");
  const [saving, setSaving] = useState(false);
  const [errorText, setErrorText] = useState("");
  const [successText, setSuccessText] = useState("");
  const [debugText, setDebugText] = useState("");
  const [form, setForm] = useState<BrandFormPayload>(defaultForm);

  useEffect(() => {
    let active = true;

    async function fetchDetail() {
      if (mode !== "edit" || !brandId) {
        setLoading(false);
        return;
      }

      try {
        const brand = (await getBrand(brandId)) as Brand;

        if (!active) return;

        setForm({
          name: brand.name ?? "",
          slug: brand.slug ?? "",
          description: brand.description ?? "",
          logo: brand.logo ?? "",
          status: toBool(brand.status),
        });
      } catch (error) {
        if (!active) return;
        setErrorText(getApiErrorMessage(error));
      } finally {
        if (active) setLoading(false);
      }
    }

    fetchDetail();

    return () => {
      active = false;
    };
  }, [brandId, mode]);

  function setField<K extends keyof BrandFormPayload>(key: K, value: BrandFormPayload[K]) {
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    if (saving) return;

    setErrorText("");
    setSuccessText("");
    setDebugText("Đã bấm submit.");

    const payload: BrandFormPayload = {
      ...form,
      name: form.name.trim(),
      slug: (form.slug || toSlug(form.name)).trim(),
      description: form.description.trim(),
      logo: form.logo.trim(),
      status: !!form.status,
    };

    if (!payload.name) {
      setErrorText("Tên thương hiệu là bắt buộc.");
      setDebugText("Bị chặn ở FE vì thiếu tên thương hiệu.");
      return;
    }

    setSaving(true);
    setDebugText("Đang gửi request tạo thương hiệu...");

    try {
      console.log("[BRAND CREATE] payload:", payload);

      if (mode === "create") {
        const saved = await createBrand(payload);
        setSuccessText("Tạo thương hiệu thành công.");
        setDebugText(`Tạo thành công brand ID = ${saved.id}`);
        router.push(`/admin/brands/${saved.id}/edit`);
        router.refresh();
        return;
      }

      await updateBrand(brandId as string, payload);
      setSuccessText("Cập nhật thương hiệu thành công.");
      setDebugText("Cập nhật thành công.");
      router.refresh();
    } catch (error) {
      console.error("[BRAND CREATE] error:", error);
      setErrorText(getApiErrorMessage(error));
      setDebugText("Request đã chạy nhưng bị lỗi.");
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        Đang tải dữ liệu thương hiệu...
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">
              {mode === "create" ? "TẠO THƯƠNG HIỆU TEST 123" : "Cập nhật thương hiệu"}
            </h1>
            <p className="mt-2 text-slate-600">
              Quản lý thương hiệu theo đúng CRUD admin.
            </p>
          </div>

          <div className="flex gap-3">
            <Link
              href="/admin/brands"
              className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
            >
              Quay lại
            </Link>

            <button
              type="submit"
              disabled={saving}
              className="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
            >
              {saving ? "Đang lưu..." : mode === "create" ? "Tạo thương hiệu" : "Lưu thay đổi"}
            </button>
          </div>
        </div>

        {errorText ? (
          <div className="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {errorText}
          </div>
        ) : null}

        {successText ? (
          <div className="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {successText}
          </div>
        ) : null}

        {debugText ? (
          <div className="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
            {debugText}
          </div>
        ) : null}
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="grid gap-4">
          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Tên thương hiệu
            </label>
            <input
              value={form.name}
              onChange={(e) => {
                const nextName = e.target.value;
                setForm((prev) => ({
                  ...prev,
                  name: nextName,
                  slug: prev.slug ? prev.slug : toSlug(nextName),
                }));
              }}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
              placeholder="Nhập tên thương hiệu"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Slug
            </label>
            <input
              value={form.slug}
              onChange={(e) => setField("slug", toSlug(e.target.value))}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
              placeholder="tu-dong-hoac-nhap-tay"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Logo
            </label>
            <input
              value={form.logo}
              onChange={(e) => setField("logo", e.target.value)}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
              placeholder="URL/path logo"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Mô tả
            </label>
            <textarea
              value={form.description}
              onChange={(e) => setField("description", e.target.value)}
              rows={6}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
            />
          </div>

          <label className="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
            <input
              type="checkbox"
              checked={form.status}
              onChange={(e) => setField("status", e.target.checked)}
            />
            <span className="text-sm text-slate-700">Hiển thị thương hiệu</span>
          </label>
        </div>
      </div>
    </form>
  );
}