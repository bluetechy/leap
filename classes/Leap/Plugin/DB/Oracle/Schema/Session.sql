/*
 * Copyright © 2011–2015 Spadefoot Team.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * Table structure for the "sessions" table
 */

CREATE TABLE "sessions" (
	"id" VARCHAR2(24) NOT NULL,
	"last_active" NUMBER(11) NOT NULL,
	"contents" VARCHAR2(255) NOT NULL,
	CONSTRAINT "sessions_id_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "sessions_last_active_idx" ON "sessions" ("last_active");
