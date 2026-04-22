import BrandForm from "../../_components/BrandForm";

export default async function AdminEditBrandPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;

  return <BrandForm mode="edit" brandId={id} />;
}