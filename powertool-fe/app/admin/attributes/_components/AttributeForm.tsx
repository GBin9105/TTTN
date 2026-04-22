"use client";

import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import {
  createAttribute,
  getAttribute,
  updateAttribute,
  type Attribute,
  type AttributeFormPayload,
} from "../../../../services/admin/attribute.service";
import { getApiErrorMessage } from "../../../../lib/api";

type AttributeFormProps = {
  mode: "create" | "edit";
  attributeId?: string;
};

type AttributeFormState = {
  name: string;
  slug: string;
  type: string;
  valuesText: string;
  status: boolean;
};

const defaultForm: AttributeFormState = {
  name: "",
  slug: "",
  type: "",
  valuesText: "",
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

function parseValues(text: string) {
  return text
    .split(/\n|,/)
    .map((item) => item.trim())
    .filter(Boolean);
}

export default function AttributeForm({
  mode,
  attributeId,
}: AttributeFormProps) {
  const router = useRouter();

  const [loading, setLoading] = useState(mode === "edit");
  const [saving, setSaving] = useState(false);
  const [errorText, setErrorText] = useState("");
  const [successText, setSuccessText] = useState("");
  const [form, setForm] = useState<AttributeFormState>(defaultForm);

  useEffect(() => {
    let active = true;

    async function fetchDetail() {
      if (mode !== "edit" || !attributeId) {
        setLoading(false);
        return;
      }

      try {
        const attribute = (await getAttribute(attributeId)) as Attribute;

        if (!active) return;

        setForm({
          name: attribute.name ?? "",
          slug: attribute.slug ?? "",
          type: attribute.type ?? "",
          valuesText: Array.isArray(attribute.values)
            ? attribute.values.join("\n")
            : "",
          status: toBool(attribute.status),
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
  }, [attributeId, mode]);

  function setField<K extends keyof AttributeFormState>(
    key: K,
    value: AttributeFormState[K]
  ) {
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    if (saving) return;

    setSaving(true);
    setErrorText("");
    setSuccessText("");

    try {
      const payload: AttributeFormPayload = {
        name: form.name.trim(),
        slug: (form.slug || toSlug(form.name)).trim(),
        type: form.type.trim(),
        values: parseValues(form.valuesText),
        status: !!form.status,
      };

      if (!payload.name) {
        throw new Error("Tên thuộc tính là bắt buộc.");
      }

      if (mode === "create") {
        const saved = await createAttribute(payload);
        setSuccessText("Tạo thuộc tính thành công.");
        router.push(`/admin/attributes/${saved.id}/edit`);
        router.refresh();
        return;
      }

      await updateAttribute(attributeId as string, payload);
      setSuccessText("Cập nhật thuộc tính thành công.");
      router.refresh();
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        Đang tải dữ liệu thuộc tính...
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">
              {mode === "create" ? "Tạo thuộc tính mới" : "Cập nhật thuộc tính"}
            </h1>
            <p className="mt-2 text-slate-600">
              Quản lý thuộc tính sản phẩm theo CRUD admin.
            </p>
          </div>

          <div className="flex gap-3">
            <Link
              href="/admin/attributes"
              className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
            >
              Quay lại
            </Link>

            <button
              type="submit"
              disabled={saving}
              className="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
            >
              {saving ? "Đang lưu..." : mode === "create" ? "Tạo thuộc tính" : "Lưu thay đổi"}
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
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="grid gap-4">
          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Tên thuộc tính
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
              placeholder="Ví dụ: Màu sắc, Kích thước, Công suất"
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
              placeholder="mau-sac"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Loại thuộc tính
            </label>
            <input
              value={form.type}
              onChange={(e) => setField("type", e.target.value)}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
              placeholder="Ví dụ: select, text, radio"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Giá trị thuộc tính
            </label>
            <textarea
              value={form.valuesText}
              onChange={(e) => setField("valuesText", e.target.value)}
              rows={8}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
              placeholder={"Mỗi giá trị một dòng\nĐỏ\nXanh\nVàng"}
            />
            <p className="mt-2 text-xs text-slate-500">
              Bạn có thể nhập mỗi giá trị một dòng hoặc ngăn cách bằng dấu phẩy.
            </p>
          </div>

          <label className="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
            <input
              type="checkbox"
              checked={form.status}
              onChange={(e) => setField("status", e.target.checked)}
            />
            <span className="text-sm text-slate-700">Hiển thị thuộc tính</span>
          </label>
        </div>
      </div>
    </form>
  );
}