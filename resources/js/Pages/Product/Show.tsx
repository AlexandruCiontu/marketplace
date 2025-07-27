import { PageProps, Product, VariationTypeOption, ProductListItem } from "@/types";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Carousel from "@/Components/Core/Carousel";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import { arraysAreEqual } from "@/helpers";
import StarRating from "@/Components/Core/StarRating";

function Show({
                appName,
                product,
                variationOptions,
                hasPurchased,
                relatedProducts,
              }: PageProps<{
  product: Product;
  variationOptions: Record<number, number>;
  hasPurchased: boolean;
  relatedProducts: ProductListItem[];
}>) {
  const form = useForm({
    option_ids: {},
    quantity: 1,
    price: null,
  });

  const [selectedOptions, setSelectedOptions] = useState<Record<number, VariationTypeOption>>({});
  const { url } = usePage();

  const images = useMemo(() => {
    for (const typeId in selectedOptions) {
      const option = selectedOptions[typeId];
      if (option?.images?.length > 0) {
        return option.images;
      }
    }
    return product?.images ?? [];
  }, [product, selectedOptions]);

  const computedProduct = useMemo(() => {
    const selectedOptionIds = Object.values(selectedOptions)
      .map((op) => op.id)
      .sort();

    let price = product.price;
    let vatRateType = product.vat_rate_type;
    let quantity = product.quantity === null ? Number.MAX_VALUE : product.quantity;

    if (Array.isArray(product.variations)) {
      for (const variation of product.variations) {
        const optionIds = variation.variation_type_option_ids.slice().sort();
        if (arraysAreEqual(selectedOptionIds, optionIds)) {
          price = variation.price;
          vatRateType = variation.vat_rate_type ?? vatRateType;
          quantity = variation.quantity === null ? Number.MAX_VALUE : variation.quantity;
          break;
        }
      }
    }

    const vatService = window?.vatService ?? null;
    const countryCode = window?.countryCode ?? 'RO';

    const result = vatService
      ? vatService.calculate(price, vatRateType, countryCode)
      : { gross: price, vat: 0 };

    return {
      price: parseFloat(Number(price).toFixed(2)),
      gross_price: parseFloat(Number(result.gross).toFixed(2)),
      vat_amount: parseFloat(Number(result.vat).toFixed(2)),
      vat_rate_type: vatRateType,
      quantity,
    };
  }, [product, selectedOptions]);

  useEffect(() => {
    if (product?.variationTypes && Object.keys(selectedOptions).length === 0) {
      product.variationTypes.forEach((type) => {
        const selectedOptionId = variationOptions?.[type.id];
        const defaultOption =
          type.options?.find((op) => op.id === selectedOptionId) ?? type.options?.[0];
        if (defaultOption) {
          chooseOption(type.id, defaultOption, false);
        }
      });
    }
  }, [product, variationOptions]);

  const getOptionIdsMap = (newOptions: Record<number, VariationTypeOption>) => {
    return Object.fromEntries(
      Object.entries(newOptions).map(([typeId, op]) => [typeId, op.id])
    );
  };

  const chooseOption = (
    typeId: number,
    option: VariationTypeOption,
    updateRouter: boolean = true
  ) => {
    setSelectedOptions((prev) => {
      const newOpts = { ...prev, [typeId]: option };

      if (updateRouter) {
        router.get(
          url,
          { options: getOptionIdsMap(newOpts) },
          { preserveScroll: true, preserveState: true }
        );
      }

      return newOpts;
    });
  };

  const onQuantityChange = (ev: React.ChangeEvent<HTMLSelectElement>) => {
    form.setData("quantity", parseInt(ev.target.value, 10));
  };

  const addToCart = () => {
    form.post(route("cart.store", product.id), {
      preserveScroll: true,
      preserveState: true,
      onError: console.error,
    });
  };

  useEffect(() => {
    form.setData("option_ids", getOptionIdsMap(selectedOptions));
  }, [selectedOptions]);

  return (
    <AuthenticatedLayout>
      <Head>
        <title>{product.title}</title>
      </Head>

      <div className="container mx-auto p-8">
        <div className="grid gap-4 sm:gap-8 grid-cols-1 lg:grid-cols-12">
          <div className="col-span-12 md:col-span-7">
            <Carousel images={images} />
          </div>

          <div className="col-span-12 md:col-span-5">
            <h1 className="text-2xl mb-2">{product.title}</h1>
            <p className="mb-4">
              by{" "}
              <Link
                href={route("vendor.profile", product.user.store_name)}
                className="hover:underline"
              >
                {product.user.name}
              </Link>{" "}
              in{" "}
              <Link
                href={route("product.byDepartment", product.department.slug)}
                className="hover:underline"
              >
                {product.department.name}
              </Link>
            </p>

            {/* Preț afișat mare + TVA */}
            <div className="mb-4">
              <div className="text-3xl font-semibold">
                <CurrencyFormatter amount={computedProduct.gross_price} />
              </div>
              <div className="text-sm text-gray-500">Price with VAT</div>
              {computedProduct.vat_amount >= 0 && (
                <div className="text-sm text-gray-500">
                  Price without VAT: <CurrencyFormatter amount={computedProduct.price} /> — VAT: <CurrencyFormatter amount={computedProduct.vat_amount} />
                </div>
              )}
            </div>

            {/* Variante (ex: mărimi, culori) */}
            {product.variationTypes && product.variationTypes.length > 0 && (
              <div className="mb-4">
                {product.variationTypes.map((type) => (
                  <div key={type.id} className="mb-2">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      {type.name}
                    </label>
                    <div className="flex flex-wrap gap-2">
                      {type.options.map((option) => {
                        const isSelected =
                          selectedOptions[type.id]?.id === option.id;
                        return (
                          <button
                            key={option.id}
                            type="button"
                            className={`px-4 py-2 border rounded ${
                              isSelected
                                ? "bg-blue-600 text-white border-blue-600"
                                : "bg-white text-gray-800 border-gray-300"
                            }`}
                            onClick={() => chooseOption(type.id, option)}
                          >
                            {option.name}
                          </button>
                        );
                      })}
                    </div>
                  </div>
                ))}
              </div>
            )}

            <div className="mb-4">
              <StarRating rating={product.average_rating ?? 0} />
            </div>

            {computedProduct.quantity < 10 && (
              <div className="text-error my-4">
                Only {computedProduct.quantity} left
              </div>
            )}

            <div className="mb-4">
              <select
                value={form.data.quantity}
                onChange={onQuantityChange}
                className="select select-bordered w-full"
              >
                {Array.from({ length: Math.min(10, computedProduct.quantity) }).map(
                  (_, i) => (
                    <option value={i + 1} key={i + 1}>
                      Quantity: {i + 1}
                    </option>
                  )
                )}
              </select>
              <button onClick={addToCart} className="btn btn-primary mt-2">
                Add to Cart
              </button>
            </div>

            <b className="text-xl">About the Item</b>
            <div
              className="wysiwyg-output overflow-x-hidden break-words"
              dangerouslySetInnerHTML={{ __html: product.description }}
            />
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

export default Show;

