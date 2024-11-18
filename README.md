# worktime
労働時間に関する計算を行う

## Install
利用するプロジェクトの `composer.json` に以下を追加する。
```composer.json
"repositories": {
    "period": {
        "type": "vcs",
        "url": "https://github.com/shimoning/worktime.git"
    }
},
```

その後以下でインストールする。

```bash
composer require shimoning/worktime
```

## Usage

### Basement
労働時間の計算を行う基本クラス。

#### diffInTime
Time オブジェクトとして結果を取得する。

**引数**
1. 開始時間
2. 終了時間

**戻値**
`Time`

#### diffInMinutes
分単位で結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 端数処理方法

**戻値**
`int` or `float`

#### diffInSeconds
秒単位で結果を取得する。

**引数**
1. 開始時間
2. 終了時間

**戻値**
`int`


### OvernightWorktime
深夜労働時間を計算するクラス。

(default: 22-5)

#### getTime
Time オブジェクトとして結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 夜間が始まる時間 (optional)
4. 早朝が終わる時間 (optional)

**戻値**
`Time`

#### getMinutes
分単位で結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 端数処理方法 (optional)
4. 夜間が始まる時間 (optional)
5. 早朝が終わる時間 (optional)

**戻値**
`int` or `float`

#### getSeconds
秒単位で結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 夜間が始まる時間 (optional)
4. 早朝が終わる時間 (optional)

**戻値**
`int`


### EarlyMorning
早朝時間にフォーカスして計算するクラス。

(default: 0-5時)

#### getTime
Time オブジェクトとして結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 早朝が終わる時間 (optional)

**戻値**
`Time`

#### getMinutes
分単位で結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 端数処理方法 (optional)
4. 早朝が終わる時間 (optional)

**戻値**
`int` or `float`

#### getSeconds
秒単位で結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 早朝が終わる時間 (optional)

**戻値**
`int`

### LateNight
夜間時間にフォーカスして計算するクラス。

#### getTime
Time オブジェクトとして結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 夜間が始まる時間 (optional)

**戻値**
`Time`

#### getMinutes
分単位で結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 端数処理方法 (optional)
4. 夜間が始まる時間 (optional)

**戻値**
`int` or `float`

#### getSeconds
秒単位で結果を取得する。

**引数**
1. 開始時間
2. 終了時間
3. 夜間が始まる時間 (optional)

**戻値**
`int`

(default: 22-24時)

-----

**ユーティリティ**

### Threshold
#### get
境目となる時間を計算する。

**引数**
1. 基準となる日時
2. 基準となる時間

### Round
#### calculate
端数処理を行う。

**引数**
1. 端数処理される値
2. 端数処理方法

## Test
`composer test`
