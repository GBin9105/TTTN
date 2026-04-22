import CategoryForm from "../../_components/CategoryForm";

export default async function AdminEditCategoryPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;

  return <CategoryForm mode="edit" categoryId={id} />;
}