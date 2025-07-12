import React, {useCallback} from 'react';
import {
  RefinementList,
  RangeInput, CurrentRefinements
} from 'react-instantsearch';
import NumberFormatter from "@/Components/Core/NumberFormatter";

const ATTRIBUTE_LABEL_MAP: any = {
  department_name: 'Department',
  category_name: 'Category',
  price: 'Price Range'
}

const FilterPanel = () => {

  const transformItems = useCallback((items: any) => {
    return items.map((item: any) => {
      return {
        ...item,
        label: ATTRIBUTE_LABEL_MAP[item.attribute] || item.label
      }
    });
  }, []);

  return (
    <div className="w-full md:w-64 lg:w-72 flex flex-col gap-6">
      <div className="card bg-base-100 shadow">
        <div className="card-body p-4">
          <h2 className="card-title text-lg">Current Filters</h2>
          <CurrentRefinements
            transformItems={transformItems}
            classNames={{
              root: 'flex flex-wrap gap-2',
              item: '',
              category: 'badge badge-primary gap-1',
              categoryLabel: 'pr-1',
              delete: 'ml-1 cursor-pointer'
            }}
          />
        </div>
      </div>

      <div className="card bg-base-100 shadow">
        <div className="card-body p-4">
          <h2 className="card-title text-lg">Department</h2>
          <RefinementList
            attribute="department_name"
            classNames={{
              root: '',
              searchBox: 'mb-2',
              list: 'space-y-1',
              item: 'flex items-center',
              label: 'label cursor-pointer justify-start gap-2 p-1',
              checkbox: 'checkbox checkbox-sm',
              labelText: 'text-sm',
              count: 'badge badge-sm ml-auto',
              showMore: 'btn btn-primary btn-sm btn-outline w-full mt-2',
              disabledShowMore: 'btn btn-sm btn-disabled w-full mt-2',
            }}
          />
          <h2 className="card-title text-lg mt-4">Category</h2>
          <RefinementList
            attribute="category_name"
            classNames={{
              root: '',
              searchBox: 'mb-2',
              list: 'space-y-1',
              item: 'flex items-center',
              label: 'label cursor-pointer justify-start gap-2 p-1',
              checkbox: 'checkbox checkbox-sm',
              labelText: 'text-sm',
              count: 'badge badge-sm ml-auto',
              showMore: 'btn btn-primary btn-sm btn-outline w-full mt-2',
              disabledShowMore: 'btn btn-sm btn-disabled w-full mt-2',
            }}
          />
          <h2 className="card-title text-lg mt-4">Price Range</h2>
          <RangeInput
            attribute="price"
            classNames={{
              root: '',
              form: 'flex items-center justify-between gap-2',
              label: 'text-sm',
              input: 'input input-bordered input-sm w-full',
              inputMin: 'input input-bordered input-sm w-full',
              inputMax: 'input input-bordered input-sm w-full',
              separator: 'text-center my-1',
              submit: 'btn btn-sm btn-primary',
            }}
          />
        </div>
      </div>
    </div>
  );
};

export default FilterPanel;
