<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class ProductController extends ResourceController
{
    private $file = WRITEPATH . 'storage/products.json';

    // Read products
    private function readProducts()
    {
        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode([]));
        }

        $data = file_get_contents($this->file);
        return json_decode($data, true);
    }

    // Save products
    private function saveProducts($products)
    {
        file_put_contents($this->file, json_encode($products, JSON_PRETTY_PRINT));
    }

    // CREATE PRODUCT
    public function create()
    {
        $input = $this->request->getJSON(true);

        $validation = $this->validateData($input, [
            'name' => 'required|max_length[255]',
            'price' => 'required|decimal|greater_than[0]',
            'quantity' => 'required|integer|greater_than_equal_to[0]',
        ]);

        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $products = $this->readProducts();

        $newProduct = [
            'id' => count($products) + 1,
            'name' => $input['name'],
            'description' => $input['description'] ?? '',
            'price' => (float)$input['price'],
            'quantity' => (int)$input['quantity']
        ];

        $products[] = $newProduct;

        $this->saveProducts($products);

        return $this->respondCreated([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => $newProduct
        ]);
    }

    // GET PRODUCT
    public function show($id = null)
    {
        $products = $this->readProducts();

        foreach ($products as $product) {
            if ($product['id'] == $id) {
                return $this->respond([
                    'status' => true,
                    'data' => $product
                ]);
            }
        }

        return $this->failNotFound('Product not found');
    }

    // UPDATE PRODUCT
   public function update($id = null)
{
    $products = $this->readProducts();

    $input = json_decode($this->request->getBody(), true);

    if (!$input || !is_array($input)) {
        return $this->fail(['error' => 'Invalid JSON body'], 400);
    }

    foreach ($products as &$product) {

        if ($product['id'] == $id) {

            // Manual validation (SAFE for CI4)
            if (isset($input['name'])) {
                if (strlen($input['name']) > 255) {
                    return $this->fail(['error' => 'Name max 255 characters'], 400);
                }
                $product['name'] = $input['name'];
            }

            if (isset($input['price'])) {
                if (!is_numeric($input['price']) || $input['price'] <= 0) {
                    return $this->fail(['error' => 'Price must be positive'], 400);
                }
                $product['price'] = (float)$input['price'];
            }

            if (isset($input['quantity'])) {
                if (!is_numeric($input['quantity']) || $input['quantity'] < 0) {
                    return $this->fail(['error' => 'Quantity must be non-negative'], 400);
                }
                $product['quantity'] = (int)$input['quantity'];
            }

            if (isset($input['description'])) {
                $product['description'] = $input['description'];
            }

            $this->saveProducts($products);

            return $this->respond([
                'status' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        }
    }

    return $this->failNotFound('Product not found');
}
}