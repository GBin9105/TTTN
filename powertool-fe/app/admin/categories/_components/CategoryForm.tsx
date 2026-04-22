"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import {
  createCategory,
  getCategoriesForSelect,
  getCategory,
  updateCategory,
  type Category,
  type CategoryFormPayload,
} from "../../../../services/admin/category.service";
import { getApiErrorMessage } from "../../../../lib/api";

type CategoryFormProps = {
  mode: "create" | "edit";
  categoryId?: string;
};

const defaultForm: CategoryFormPayload = {
  parent_id: null,
  name: "",
  slug: "",
  description: "",
  image: "",
  status: true,
  sort_order: 0,
};

function toBool(value: unknown) {
  return value === true || value === 1 || value === "1";
}

export default function CategoryForm({ mode, categoryId }: CategoryFormProps) {
  const router = useRouter();

  const [loading, setLoading] = useState(mode === "edit");
  const [saving, setSaving] = useState(false);
  const [errorText, setErrorText] = useState("");
  const [successText, setSuccessText] = useState("");
  const [form, setForm] = useState<CategoryFormPayload>(defaultForm);
  const [allCategories, setAllCategories] = useState<Category[]>([]);

  useEffect(() => {
    let active = true;

    async function bootstrap() {
      try {
        const categoryOptions = await getCategoriesForSelect();

        if (!active) return;
        setAllCategories(categoryOptions);

        if (mode === "edit" && categoryId) {
          const category = (await getCategory(categoryId)) as Category;

          if (!active) return;

          setForm({
            parent_id: category.parent_id ?? null,
            name: category.name ?? "",
            slug: category.slug ?? "",
            description: category.description ?? "",
            image: category.image ?? "",
            status: toBool(category.status),
            sort_order: Number(category.sort_order ?? 0),
          });
        }
      } catch (error) {
        if (!active) return;
        setErrorText(getApiErrorMessage(error));
      } finally {
        if (active) setLoading(false);
      }
    }

    bootstrap();

    return () => {
      active = false;
    };
  }, [categoryId, mode]);

  function setField<K extends keyof CategoryFormPayload>(key: K, value: CategoryFormPayload[K]) {
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  async function handleSave() {
    setSaving(true);
    setErrorText("");
    setSuccessText("");

    try {
      if (!form.name.trim()) {
        throw new Error("Tên danh mục là bắt buộc.");
      }

      let saved: Category;

      if (mode === "create") {
        saved = await createCategory(form);
        setSuccessText("Tạo danh mục thành công.");
        router.push(`/admin/categories/${saved.id}/edit`);
        return;
      }

      saved = await updateCategory(categoryId as string, form);
      setSuccessText("Cập nhật danh mục thành công.");

      setForm({
        parent_id: saved.parent_id ?? null,
        name: saved.name ?? "",
        slug: saved.slug ?? "",
        description: saved.description ?? "",
        image: saved.image ?? "",
        status: toBool(saved.status),
        sort_order: Number(saved.sort_order ?? 0),
      });
    } catch (error) {
      setErrorText(getApiErrorMessage(error));
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        Đang tải dữ liệu danh mục...
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">
              {mode === "create" ? "Tạo danh mục mới" : "Cập nhật danh mục"}
            </h1>
            <p className="mt-2 text-slate-600">
              Quản lý danh mục theo đúng CRUD admin.
            </p>
          </div>

          <div className="flex gap-3">
            <button
              type="button"
              onClick={() => router.push("/admin/categories")}
              className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
            >
              Quay lại
            </button>

            <button
              type="button"
              onClick={handleSave}
              disabled={saving}
              className="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
            >
              {saving ? "Đang lưu..." : mode === "create" ? "Tạo danh mục" : "Lưu thay đổi"}
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
              Danh mục cha
            </label>
            <select
              value={form.parent_id ?? ""}
              onChange={(e) =>
                setField("parent_id", e.target.value ? Number(e.target.value) : null)
              }
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
            >
              <option value="">-- Không có danh mục cha --</option>
              {allCategories
                .filter((item) => String(item.id) !== String(categoryId))
                .map((item) => (
                  <option key={item.id} value={item.id}>
                    {item.name}
                  </option>
                ))}
            </select>
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Tên danh mục
            </label>
            <input
              value={form.name}
              onChange={(e) => setField("name", e.target.value)}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
              placeholder="Nhập tên danh mục"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Slug
            </label>
            <input
              value={form.slug}
              onChange={(e) => setField("slug", e.target.value)}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
              placeholder="tu-dong-hoac-nhap-tay"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Ảnh danh mục
            </label>
            <input
              value={form.image}
              onChange={(e) => setField("image", e.target.value)}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
              placeholder="URL/path ảnh"
            />
          </div>

          <div>
            <label className="mb-2 block text-sm font-medium text-slate-800">
              Thứ tự sắp xếp
            </label>
            <input
              type="number"
              value={form.sort_order}
              onChange={(e) => setField("sort_order", Number(e.target.value))}
              className="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
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
            <span className="text-sm text-slate-700">Hiển thị danh mục</span>
          </label>
        </div>
      </div>
    </div>
  );
}