import { api } from "../../lib/api";

export type Attribute = {
  id: number;
  name: string;
  slug?: string | null;
  type?: string | null;
  values?: string[] | null;
  status?: boolean | number;
  created_at?: string;
  updated_at?: string;
};

export type AttributeFormPayload = {
  name: string;
  slug: string;
  type: string;
  values: string[];
  status: boolean;
};

export type AttributeListParams = {
  page?: number;
  per_page?: number;
  q?: string;
};

export type PaginatedResult<T> = {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

type ApiEnvelope<T> = {
  success?: boolean;
  message?: string;
  data?: T;
};

function normalizePaginated<T>(
  payload: ApiEnvelope<PaginatedResult<T>> | PaginatedResult<T> | T[]
): PaginatedResult<T> {
  const maybeWrapped = payload as ApiEnvelope<PaginatedResult<T>>;
  const data = maybeWrapped?.data ?? payload;

  if (Array.isArray(data)) {
    return {
      data,
      current_page: 1,
      last_page: 1,
      per_page: data.length,
      total: data.length,
    };
  }

  return {
    data: Array.isArray((data as PaginatedResult<T>)?.data)
      ? (data as PaginatedResult<T>).data
      : [],
    current_page: Number((data as PaginatedResult<T>)?.current_page ?? 1),
    last_page: Number((data as PaginatedResult<T>)?.last_page ?? 1),
    per_page: Number((data as PaginatedResult<T>)?.per_page ?? 10),
    total: Number((data as PaginatedResult<T>)?.total ?? 0),
  };
}

function normalizeItem<T>(payload: ApiEnvelope<T> | T): T {
  const maybeWrapped = payload as ApiEnvelope<T>;
  return (maybeWrapped?.data ?? payload) as T;
}

export async function getAttributes(params: AttributeListParams = {}) {
  const response = await api.get("/api/v1/admin/attributes", { params });
  return normalizePaginated<Attribute>(response.data);
}

export async function getAttribute(id: string | number) {
  const response = await api.get(`/api/v1/admin/attributes/${id}`);
  return normalizeItem<Attribute>(response.data);
}

export async function createAttribute(payload: AttributeFormPayload) {
  const response = await api.post("/api/v1/admin/attributes", payload);
  return normalizeItem<Attribute>(response.data);
}

export async function updateAttribute(
  id: string | number,
  payload: AttributeFormPayload
) {
  const response = await api.put(`/api/v1/admin/attributes/${id}`, payload);
  return normalizeItem<Attribute>(response.data);
}

export async function deleteAttribute(id: string | number) {
  const response = await api.delete(`/api/v1/admin/attributes/${id}`);
  return response.data;
}