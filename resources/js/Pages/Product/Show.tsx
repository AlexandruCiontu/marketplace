import {PageProps, Product, VariationTypeOption, Image} from "@/types";
import {Head, router, useForm, usePage} from "@inertiajs/react";
import {useEffect, useMemo, useRef, useState} from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import ProductGallery from "@/Components/Core/Carousel";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import {arraysAreEqual} from "@/helpers";
import route from 'ziggy-js';

import RatingSummary from "@/Components/RatingSummary";
import ReviewList from "@/Components/ReviewList";
import ReviewForm from "@/Components/ReviewForm";
import Carousel from "@/Components/ui/Carousel";
import ProductCardMini from "@/Components/Product/ProductCardMini";

function Show({
  appName, product, variationOptions,
  can_review, already_reviewed,
  bought_together = [], similar_products = [], compare_products = [], also_viewed = []
}: PageProps<{
  product: Product,
  variationOptions: number[]
  can_review?: boolean
  already_reviewed?: boolean
  bought_together?: any[]
  similar_products?: any[]
  compare_products?: any[]
  also_viewed?: any[]
}>) {
  const form = useForm<{ option_ids: Record<string, number>; quantity: number; price: number | null; }>({
    option_ids: {},
    quantity: 1,
    price: null
  });

  const {url} = usePage();
  const [selectedOptions, setSelectedOptions] =
    useState<Record<number, VariationTypeOption>>({});

  const images = useMemo<Image[]>(() => {
    for (let typeId in selectedOptions) {
      const option = selectedOptions[typeId];
      if (option?.images && option.images.length > 0) return [...option.images];
    }
    return [...(product.images ?? [])];
  }, [product, selectedOptions]);

  const [computedProduct, setComputedProduct] = useState<{
    price: number; price_gross: number; vat_amount: number; vat_rate: number; vat_type: string; quantity: number
  }>({
    price: product.price,
    price_gross: product.price_gross,
    vat_amount: product.vat_amount,
    vat_rate: product.vat_rate,
    vat_type: product.vat_type,
    quantity: product.quantity ?? Number.MAX_VALUE,
  });

  useEffect(() => {
    const selectedOptionIds = Object.values(selectedOptions)
      .map(op => op.id)
      .sort();

    let price = product.price;
    let quantity = product.quantity === null ? Number.MAX_VALUE : product.quantity;

    for (let variation of product.variations) {
      const optionIds = [...variation.variation_type_option_ids].sort();
      if (arraysAreEqual(selectedOptionIds, optionIds)) {
        price = variation.price;
        quantity = variation.quantity === null ? Number.MAX_VALUE : variation.quantity;
        break;
      }
    }

    fetch(`/api/products/${product.id}/price?price=${price}`, { credentials:'same-origin' })
      .then(res => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(data => setComputedProduct({
        price,
        price_gross: data.price_gross ?? price,
        vat_amount: data.vat_amount ?? 0,
        vat_rate: data.vat_rate ?? 0,
        vat_type: product.vat_type,
        quantity,
      }))
      .catch(() => setComputedProduct(cp => ({ ...cp, price, price_gross: price, vat_amount: 0, quantity })));
  }, [selectedOptions]);

  useEffect(() => {
    for (let type of product.variationTypes) {
      const selectedOptionId: number = variationOptions[type.id];
      chooseOption(
        type.id,
        type.options.find(op => op.id == selectedOptionId) || type.options[0],
        false
      );
    }
  }, []);

  const getOptionIdsMap = (newOptions: object) => (
    Object.fromEntries(Object.entries(newOptions).map(([a, b]: any) => [a, b.id]))
  );

  const chooseOption = (typeId: number, option: VariationTypeOption, updateRouter: boolean = true) => {
    setSelectedOptions(prev => {
      const newOptions = { ...prev, [typeId]: option };
      if (updateRouter) {
        router.get(url, { options: getOptionIdsMap(newOptions) }, { preserveScroll: true, preserveState: true });
      }
      return newOptions;
    });
  };

  const onQuantityChange = (ev: React.ChangeEvent<HTMLSelectElement>) => {
    form.setData('quantity', parseInt(ev.target.value));
  };

  const addToCart = () => {
    form.post(route('cart.store', product.id), {
      preserveScroll: true,
      preserveState: true,
      onError: (err) => console.log(err)
    });
  };

  const renderProductVariationTypes = () => {
    return product.variationTypes.map((type, i) => (
      <div key={type.id}>
        <b>{type.name}</b>
        {type.type === 'Image' &&
          <div className="flex gap-2 mb-4">
            {type.options.map(option => (
              <div onClick={() => chooseOption(type.id, option)} key={option.id}>
                {!!option.images?.length &&
                  <img src={option.images[0].thumb}
                       alt=""
                       className={'w-[64px] h-[64px] object-contain ' + (
                         selectedOptions[type.id]?.id === option.id ? 'outline outline-4 outline-primary' : ''
                       )}/>
                }
              </div>
            ))}
          </div>}
        {type.type === 'Radio' &&
          <div className="flex join mb-4">
            {type.options.map(option => (
              <input onChange={() => chooseOption(type.id, option)}
                     key={option.id}
                     className="join-item btn"
                     type="radio"
                     value={option.id}
                     checked={selectedOptions[type.id]?.id === option.id}
                     name={'variation_type_' + type.id}
                     aria-label={option.name}/>
            ))}
          </div>}
      </div>
    ));
  };

  const renderAddToCartButton = () => (
    <div className="mb-8 flex gap-4">
      <select value={form.data.quantity} onChange={onQuantityChange} className="select select-bordered w-full">
        {Array.from({length: Math.min(10, computedProduct.quantity)}).map((_, i) => (
          <option value={i + 1} key={i + 1}>Quantity: {i + 1}</option>
        ))}
      </select>
      <button onClick={addToCart} className="btn btn-primary">Add to Cart</button>
    </div>
  );

  const ProductDetails = () => (
    <div>
      <RatingSummary
        average={(product as any).rating_average ?? (product as any).reviews_avg_rating ?? 0}
        count={(product as any).reviews_count ?? 0}
        onClick={() => {
          setShowAllReviews(true);
          setTimeout(()=>reviewsRef.current?.scrollIntoView({behavior:'smooth', block:'start'}), 0);
        }}
      />

      {renderProductVariationTypes()}

      {computedProduct.quantity != undefined && computedProduct.quantity < 10 && (
        <div className="text-error my-4">
          <span>Only {computedProduct.quantity} left</span>
        </div>
      )}

      {renderAddToCartButton()}

      {product.weight && (
        <div className="mb-8">
          <h2 className="font-semibold text-gray-700 text-lg">Product Details</h2>
          <ul className="text-gray-600 list-disc list-inside space-y-1">
            <li>Weight: {product.weight} kg</li>
            <li>Length: {product.length} cm</li>
            <li>Width: {product.width} cm</li>
            <li>Height: {product.height} cm</li>
          </ul>
        </div>
      )}
    </div>
  );

  useEffect(() => {
    const idsMap = Object.fromEntries(
      Object.entries(selectedOptions).map(([typeId, option]: [string, VariationTypeOption]) => [typeId, option.id])
    );
    form.setData('option_ids', idsMap);
  }, [selectedOptions]);

  const reviewsRef = useRef<HTMLDivElement|null>(null);
  const [showAllReviews, setShowAllReviews] = useState(false);

  const toCards = (list:any[] = []) => list.map(p => (
    <ProductCardMini
      key={p.id}
      id={p.id}
      name={p.name}
      slug={p.slug}
      image_url={p.image_url}
      price_gross={p.price_gross}
      reviews_count={p.reviews_count}
      reviews_avg_rating={p.reviews_avg_rating}
    />
  ));

  return (
    <AuthenticatedLayout>
      <Head>
        <title>{product.title}</title>
        <meta name="title" content={product.meta_title || product.title}/>
        <meta name="description" content={product.meta_description}/>
        <link rel="canonical" href={route('product.show', product.slug)}/>
        <meta property="og:title" content={product.title}/>
        <meta property="og:description" content={product.meta_description}/>
        <meta property="og:image" content={images[0]?.medium || images[0]?.small || images[0]?.url}/>
        <meta property="og:url" content={route('product.show', product.slug)}/>
        <meta property="og:type" content="product"/>
        <meta property="og:site_name" content={appName}/>
      </Head>

      <div className="container mx-auto p-8">
        <div className="space-y-12">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <ProductGallery images={images} />
            <div>
              <h1 className="text-2xl font-bold">{product.title}</h1>
              <p className="text-gray-600">
                {product.user.name} in {product.department.name}
              </p>
              <p className="text-3xl font-semibold mt-4">
                <CurrencyFormatter amount={computedProduct.price_gross ?? 0} />
              </p>
              {computedProduct.vat_amount && computedProduct.vat_amount > 0 && (
                <p className="text-sm text-gray-500">
                  Includes VAT: <CurrencyFormatter amount={computedProduct.vat_amount ?? 0} /> ({computedProduct.vat_rate ?? 0}%)
                </p>
              )}
              <ProductDetails />
            </div>
          </div>

          <Carousel title="Frequently bought together" items={toCards(bought_together)} />
          <Carousel title="You may also like" items={toCards(similar_products)} />
          <Carousel title="Compare with similar products" items={toCards(compare_products)} />
          <Carousel title="Customers also viewed" items={toCards(also_viewed)} />

          <div className="prose max-w-none">
            <h2>About the Item</h2>
            <div dangerouslySetInnerHTML={{ __html: product.description }} />
          </div>

          <section ref={reviewsRef} id="reviews" className="space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold">Reviews</h2>
              <div className="text-sm opacity-70">
                {(product as any).reviews_count ?? 0} reviews • average {((product as any).rating_average ?? (product as any).reviews_avg_rating ?? 0).toFixed(1)}/5
              </div>
            </div>

            <ReviewList
              reviews={(product as any)?.reviews ?? []}
              limit={showAllReviews ? undefined : 5}
            />

            {can_review && !already_reviewed && (
              <div className="mt-6">
                <h3 className="font-medium mb-2">Leave a review</h3>
                <ReviewForm postUrl={route('products.reviews.store', product.id)} />
              </div>
            )}
          </section>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

export default Show;

