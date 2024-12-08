<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="ezbooking",
 *      description="訂購系統 api description",
 *
 *      @OA\Contact(
 *          email="developer@example.com"
 *      ),
 *
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 *
 * @OA\PathItem(
 *      path="/"
 *  )
 *
 * * @OA\server(
 *      url = "http://ezbooking.test",
 *      description="測試區"
 * )
 * @OA\server(
 *      url = "http://ezbooking.test",
 *      description="localhost"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 * )
 *
 * @OA\Components(
 *
 *     @OA\Response(
 *          response="200",
 *          description="成功",
 *
 *          @OA\JsonContent(
 *              example={
 *                  "status":200,
 *                  "message":"OK",
 *              }
 *          ),
 *     ),
 *
 *     @OA\Response(
 *         response="400",
 *         description="客戶端錯誤",
 *
 *         @OA\JsonContent(
 *               example={
 *                   "status":400,
 *                   "message":"客戶端錯誤",
 *               }
 *           ),
 *     ),
 *
 *     @OA\Response(
 *          response="401",
 *          description="身份驗證失敗",
 *
 *          @OA\JsonContent(
 *              example={
 *                  "status":401,
 *                  "message":"Unauthorized",
 *              }
 *          ),
 *     ),
 *
 *     @OA\Response(
 *          response="404",
 *          description="找不到請求的資源",
 *
 *          @OA\JsonContent(
 *              example={
 *                  "status":404,
 *                  "message":"Not Found",
 *              }
 *          ),
 *     ),
 *
 *     @OA\Response(
 *          response="405",
 *          description="不支援此方法",
 *
 *          @OA\JsonContent(
 *              example={
 *                  "status":405,
 *                  "message":"Method Not Allowed",
 *              }
 *          ),
 *     ),
 *
 *     @OA\Response(
 *          response="500",
 *          description="伺服器發生錯誤",
 *
 *          @OA\JsonContent(
 *              example={
 *                  "status":500,
 *                  "message":"伺服器發生錯誤",
 *              }
 *          ),
 *     ),
 *
 * )
 */
abstract class Controller
{
    //
}
