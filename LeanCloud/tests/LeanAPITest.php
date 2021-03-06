<?php
use LeanCloud\LeanClient;
use LeanCloud\CloudException;

/**
 * Testing API behaviors
 */

class LeanAPITest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        LeanClient::initialize(
            getenv("LC_APP_ID"),
            getenv("LC_APP_KEY"),
            getenv("LC_APP_MASTER_KEY"));
        LeanClient::useRegion(getenv("LC_API_REGION"));
    }

    public function testIncrementOnStringField() {
        $obj = array("name" => "alice");
        $resp = LeanClient::post("/classes/TestObject", $obj);

        $this->setExpectedException("LeanCloud\CloudException",
                                    "111 Invalid value type for field", 111);
        $resp2 = LeanClient::put("/classes/TestObject/" . $resp["objectId"],
                                 array("name" => array("__op" => "Increment",
                                                       "amount" => 1)));

        LeanClient::delete("/classes/TestObject/{$resp['objectId']}");
    }

    /**
     * Increment a field on new object is allowed.
     */
    public function testIncrementOnNewObject() {
        $obj = array("name" => "alice",
                     "score" => array("__op" => "Increment",
                                      "amount" => 1));
        $resp = LeanClient::post("/classes/TestObject", $obj);
        $this->assertNotEmpty($resp["objectId"]);
    }

    public function testAddOnNewObject() {
        $obj = array("name" => "alice",
                     "tags" => array("__op" => "Add",
                                     "objects" => array("frontend")));
        $resp = LeanClient::post("/classes/TestObject", $obj);
        $this->assertNotEmpty($resp["objectId"]);

        LeanClient::delete("/classes/TestObject/{$resp['objectId']}");
    }

    public function testAddUniqueOnAddField() {
        $obj = array("name" => "alice",
                     "tags" => array("__op" => "Add",
                                     "objects" => array("frontend", "frontend")));
        $resp = LeanClient::post("/classes/TestObject", $obj);
        $this->assertNotEmpty($resp["objectId"]);

        $resp2 = LeanClient::get("/classes/TestObject/{$resp["objectId"]}");
        $this->assertEquals(array("frontend", "frontend"), $resp2["tags"]);

        $resp3 = LeanClient::put("/classes/TestObject/{$resp["objectId"]}",
                                 array("tags" => array("__op" => "AddUnique",
                                                       "objects" => array("css"))));
        // AddUnique will not remove exsiting duplicate items
        $resp4 = LeanClient::get("/classes/TestObject/{$resp["objectId"]}");
        $this->assertEquals(array("frontend", "frontend", "css"),
                            $resp4["tags"]);

        LeanClient::delete("/classes/TestObject/{$resp['objectId']}");
    }

    public function testHeterogeneousObjectsInArray() {
        $obj = array("name" => "alice",
                     "tags" => array("foo", 42, array("a", "b")));
        $resp = LeanClient::post("/classes/TestObject", $obj);
        $this->assertNotEmpty($resp["objectId"]);

        LeanClient::delete("/classes/TestObject/{$resp['objectId']}");
    }

    public function testSetHashValue() {
        $obj = array("name" => "alice",
                     "attr" => array("age" => 12,
                                     "gender" => "female"));
        $resp = LeanClient::post("/classes/TestObject", $obj);
        $this->assertNotEmpty($resp["objectId"]);

        // Add hash pair to hash field is not valid
        $this->setExpectedException("LeanCloud\CloudException", null, 1);
        $resp2 = LeanClient::put("/classes/TestObject/{$resp["objectId"]}",
                                 array("attr" => array(
                                     "__op" => "add",
                                     "objects" => array("favColor" => "Orange"))));
        LeanClient::delete("/classes/TestObject/{$resp['objectId']}");
    }

    public function testAddRelation() {
        $adds = array("__op" => "AddRelation",
                      "objects" => array(
                          array("__type" => "Pointer",
                                "className" => "TestObject",
                                "objectId" => "abc001")));
        $obj = array("name" => "alice",
                     "likes" => $adds);
        $resp = LeanClient::post("/classes/TestObject", $obj);
        $this->assertNotEmpty($resp["objectId"]);

        LeanClient::delete("/classes/TestObject/{$resp['objectId']}");
    }

    public function testRelationBatchOp() {
        $adds = array("__op" => "AddRelation",
                      "objects" => array(
                          array("__type" => "Pointer",
                                "className" => "TestObject",
                                "objectId" => "abc001")));
        $removes = array("__op" => "RemoveRelation",
                      "objects" => array(
                          array("__type" => "Pointer",
                                "className" => "TestObject",
                                "objectId" => "abc002")));
        $obj = array("name" => "alice",
                     "likes" => array("__op" => "Batch",
                                      "ops" => array($adds, $removes)));
        $this->setExpectedException("LeanCloud\CloudException", null, 301);
        $resp = LeanClient::post("/classes/TestObject", $obj);
        // $this->assertNotEmpty($resp["objectId"]);
        // LeanClient::delete("/classes/TestObject/{$resp['objectId']}");
    }

    /**
     * Batch on array operation will result error:
     *
     * 301 - Fails to insert new document, cannot update on ...
     *       at the same time.
     */
    public function testBatchOperationOnArray() {
        $obj = array("name" => "Batch test", "tags" => array());
        $resp = LeanClient::post("/classes/TestObject", $obj);
        $this->assertNotEmpty($resp["objectId"]);


        $adds    = array("__op" => "Add",
                         "objects" => array("javascript", "frontend"));
        $removes = array("__op" => "Remove",
                         "objects" => array("frontend", "css"));
        $obj     = array("tags" => array("__op" => "Batch",
                                         "ops"  => array($adds, $removes)));

        $this->setExpectedException("LeanCloud\CloudException", null, 301);
        $resp = LeanClient::put("/classes/TestObject/{$resp['objectId']}",
                                $obj);

        LeanClient::delete("/classes/TestObject/{$obj['objectId']}");
    }

    public function testBatchGet() {
        $obj1 = array("name" => "alice 1");
        $obj2 = array("name" => "alice 2");
        $resp1 = LeanClient::post("/classes/TestObject", $obj1);
        $resp2 = LeanClient::post("/classes/TestObject", $obj2);
        $this->assertNotEmpty($resp1["objectId"]);
        $this->assertNotEmpty($resp2["objectId"]);

        $req[] = array("path" => "/1.1/classes/TestObject/{$resp1['objectId']}",
                       "method" => "GET");
        $req[] = array("path" => "/1.1/classes/TestObject/{$resp2['objectId']}",
                       "method" => "GET");
        $resp = LeanClient::post("/batch", array("requests" => $req));
        $this->assertEquals(2, count($resp));
        $this->assertEquals($resp1["objectId"], $resp[0]["success"]["objectId"]);
        $this->assertEquals($resp2["objectId"], $resp[1]["success"]["objectId"]);
    }

    public function testBatchGetNotFound() {
        $obj = array("name" => "alice");
        $resp = LeanClient::post("/classes/TestObject", $obj);
        $this->assertNotEmpty($resp["objectId"]);
        $req[] = array("path" => "/1.1/classes/TestObject/{$resp['objectId']}",
                       "method" => "GET");
        $req[] = array("path" => "/1.1/classes/TestObject/nonexistent_id",
                       "method" => "GET");
        $resp2 = LeanClient::batch($req);
        $this->assertNotEmpty($resp2[0]["success"]);
        $this->assertEmpty($resp2[1]["success"]); // empty when not found

        LeanClient::delete("/classes/TestObject/{$resp['objectId']}");
    }

    public function testUserLogin() {
        $data = array("username" => "testuser",
                      "password" => "5akf#a?^G",
                      "phone" => "18612340000");
        $resp = LeanClient::post("/users", $data);
        $this->assertNotEmpty($resp["objectId"]);
        $this->assertNotEmpty($resp["sessionToken"]);
        $id = $resp["objectId"];
        $resp = LeanClient::get("/users/me",
                                array("session_token" => $resp["sessionToken"]));
        $this->assertNotEmpty($resp["objectId"]);
        LeanClient::delete("/users/{$id}", $resp["sessionToken"]);

        // Raise 211: Could not find user.
        $this->setExpectedException("LeanCloud\CloudException", null, 211);
        $resp = LeanClient::get("/users/me",
                                array("session_token" => "non-existent-token"));
    }

}

