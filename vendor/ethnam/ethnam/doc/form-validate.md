# フォーム値の自動検証を行う(基本編)
  - (1) 属性を設定する 
    - type属性 
    - 制限属性 
    - VAR_TYPE_DATETIME に関する注意事項 
    - VAR_TYPE_STRING の max, min属性に関する注意事項 
    - 制限属性(配列使用時) 
    - 補足属性 
    - 属性設定例 
  - (2) validate()メソッドを実行する 
  - (3) エラーメッセージを表示する 
    - エラーメッセージ一覧 
    - 特定のフォームに対するエラーメッセージ 
  - 補足 

## フォーム値の自動検証を行う(基本編)

クライアントから送信されたフォーム値の検証は、ウェブアプリケーションにおいて重要な、そして面倒な処理の1つです。Ethnaではこの処理を出来る限り手間をかけずに行えるような自動検証機能を提供しています。

フォーム値の自動検証を行う手順は簡単で、以下のようになります。

1. アクションフォームの$formメンバにフォーム値の属性(受け取りたい値の型や最大値等)を設定します
2. アクションフォームオブジェクトのvalidate()メソッドを実行します
3. エラーメッセージを表示します

アクションフォームのvalidate()メソッドは、1.で設定した属性に基づいて、入力されたフォーム値を検証し、エラーが発生すると(つまり、属性で指定した制限を超えた値が入力されると)エラーをアクションエラーオブジェクトに登録します\*1。

validate()メソッドは戻り値として発生したエラーの数を返すので、1以上の値が返された場合は入力値でエラーが発生したと判断して、エラー用画面を表示します\*2。

具体的な手順については以下を御覧下さい。

### (1) 属性を設定する

自動検証を行うには、まず属性としてフォーム値の型を指定します。

1. type(フォーム値の型)

そして、自動検証で設定可能な属性は以下の4通りとなります(不要な属性は当然省略可能です)。

1. required(必須チェック)
2. min(最小文字数(バイト数)チェック)
3. max(最大文字数(バイト数)チェック)
4. regexp(正規表現によるチェック)
5. mbregexp(マルチバイト対応正規表現によるチェック(2.3.2 以降))

また、補助的な値として以下の2つを設定することが出来ます。

1. name(エラーメッセージ表示時等のための、表示用フォーム名)
2. form_type(エラーメッセージ表示等のためのフォーム種別\*3)

上記に加えて、任意のメソッドによるチェックも可能です(メールアドレス、URL、アプリケーション固有ID等)。詳細は [フォーム値の自動検証を行う(カスタムチェック編)](form-customvalidate.md)を御覧下さい。

#### type属性

type属性に設定可能な値は以下の通りとなりますので、受け取りたい値に応じて設定します。型として特に制限を設けない場合にはVAR_TYPE_STRINGを設定します。

| VAR_TYPE_INT | 整数(+/-) |
| VAR_TYPE_FLOAT | 小数(+/-) |
| VAR_TYPE_STRING | 文字列 |
| VAR_TYPE_DATETIME | 日付(YYYY/MM/DD HH:MM:SS等) |
| VAR_TYPE_BOOLEAN | 真偽値(1 or 0) |
| VAR_TYPE_FILE | ファイル |

#### 制限属性

required/min/max/regexpの各属性はtype属性に設定された値によって意味合いが変化します。詳細は以下の通りです。

| type属性 | required属性 | min属性 | max属性 | (mb)regexp属性 |
| VAR_TYPE_INT | 必須チェック | 数値としての最小値 | 数値としての最大値 | 正規表現 |
| VAR_TYPE_FLOAT | 必須チェック | 数値としての最小値 | 数値としての最大値 | 正規表現 |
| VAR_TYPE_STRING | 必須チェック | 最小文字(バイト)数 | 最大文字(バイト)数 | 正規表現 |
| VAR_TYPE_DATETIME | 必須チェック | 入力可能な最も古い日付 | 入力可能な最も新しい日付 | 正規表現 |
| VAR_TYPE_BOOLEAN | 必須チェック | - | - | - |
| VAR_TYPE_FILE | 必須チェック | ファイルの最小サイズ(KB) | ファイルの最大サイズ(KB) | - |

#### VAR_TYPE_DATETIME に関する注意事項

type 属性に VAR_TYPE_DATETIME を指定する場合は、PHP の [strtotime関数](http://jp.php.net/strtotime) が動作する英文形式の入力があることを期待することに注意して下さい。そのため、日本語等のマルチバイト文字が含まれた日付等では max, min 属性は動作しません\*4。また、負のUnixタイムスタンプに対応しているかどうか、そしてサポートするタイムスタンプの範囲もプラットフォーム依存です。

よって、こうした制限事項にひっかかるような日付の入力値の検証を行いたい場合は、VAR_TYPE_DATETIME は使わないで下さい。その場合は、年・月・日 などのフィールドをそれぞれフォーム定義で指定するなどして、カスタムバリデータを書いたほうが無難です。

#### VAR_TYPE_STRING の max, min属性に関する注意事項

Ethna 2.5.0 以降では、VAR_TYPE_STRING のフォーム定義に対して maxとmin の属性を設定するとデフォルトで最大（最小）文字数のチェックが行われるようになりました。これに対して 2.3.x より前のバージョンでは、最大（最小）バイト数でチェックを行います。

2.5.0 以降でバイト数によるチェックを行いたい場合は、 [VAR_TYPE_STRING の max, min 属性に関する詳細](form-validate-vartypestring.md) を参照して下さい。

#### 制限属性(配列使用時)

type属性に **配列が指定されている場合** は、以下のルールに従って自動検証が行われます。

- required属性の場合  
  
required 属性を true にすると、配列の場合はデフォルトで **Submitされた配列の全ての要素** が入力されていなければなりません。  
  
「特定の数以上の要素」が入力されなければならない場合は、'required' => true の指定に加え、以下のように _required_num_ 属性を指定します。  
  

    $form = array(
        'sample' => array(
            'type' => array(VAR_TYPE_INT),
            'form_type' => FORM_TYPE_TEXT,
            'required' => true,
            'required_num' => 2, // sampleには2個以上の入力が必須
        ),
    );

  
また、特定の要素（例：2番目の要素と3番目の要素）のみ入力を必須にしたい場合もあると思います。その場合は、'required' => true の指定に加え、以下の通り _required_key_ 要素を指定します。この場合は 最初の要素を「0」として、その後順番に必要な要素の位置を指定します。  
  

    $form = array(
        'sample' => array(
            'type' => array(VAR_TYPE_INT),
            'form_type' => FORM_TYPE_TEXT,
            'required' => true,
            'required_key' => array(0,2,4), // 1番目, 3番目, 5番目の要素入力が必須。
        ),
    );

- required 属性以外の要素は、入力された各要素に対して、指定された属性を満たすかどうかのチェックが行われます。

#### 補足属性

name属性にはフォームの表示名(フォーム名が'mailaddress'なら'メールアドレス'のようになる)を、form_type属性にはフォームの種別を設定します。form_typeに設定可能な値は以下の通りです。この属性は、フォームヘルパで特に重要です。

[フォームヘルパのページ](view-form_helper.md) も参照してください。

| FORM_TYPE_TEXT | テキストボックス |
| FORM_TYPE_PASSWORD | パスワード |
| FORM_TYPE_TEXTAREA | テキストエリア |
| FORM_TYPE_SELECT | セレクトボックス |
| FORM_TYPE_RADIO | ラジオボタン |
| FORM_TYPE_CHECKBOX | チェックボックス |
| FORM_TYPE_BUTTON | ボタン |
| FORM_TYPE_FILE | ファイル |
| FORM_TYPE_HIDDEN | 隠れコントロール |

#### 属性設定例

以下に、幾つかの設定例を挙げますので、ご参考にして下さい。

sampleというテキストボックス(表示名「サンプル」)に16〜32文字の英字のみ許可(必須):

    $form = array(
        'sample' => array(
            'name' => 'サンプル',
            'required' => true,
            'min' => 16,
            'max' => 32,
            'regexp' => '/^[a-zA-Z]+$/',
            'form_type' => FORM_TYPE_TEXT,
            'type' => VAR_TYPE_STRING,
        ),
    );

foobar というテキストボックスに、全角ひらがなのみを入力することを許可する場合（必須）  
**regexp 属性と異なり、正規表現にスラッシュを付ける必要がないことに注意して下さい。** \*5

    $form = array(
        'foobar' => array(
            'name' => 'ひらがなのみを許可するテキストボックス',
            'required' => true,
            'mbregexp' => '^[ぁ-んー]+$', // 正規表現 前後にスラッシュは不要！
            'mbregexp_encoding' => 'UTF-8', // マッチさせる文字列のエンコーディング
            'form_type' => FORM_TYPE_TEXT,
            'type' => VAR_TYPE_STRING,
        ),
    );

question[]というチェックボックス(表示名「質問」):

    $form = array(
        'question' => array(
            'name' => '質問',
            'form_type' => FORM_TYPE_CHECKBOX,
            'type' => array(VAR_TYPE_BOOLEAN),
        ),
    );

### (2) validate()メソッドを実行する

上記のようにフォーム値を定義したら、あとはvalidate()メソッドを実行するだけです。validate()メソッドは、各アクションのprepare()メソッドで実行します。具体的には以下のようになります。

    class Sample_Action_LoginDo extends Ethna_ActionClass
    {
    ...
        function prepare()
        {
            if ($this->af->validate() > 0) {
                // フォーム値の自動検証でエラーが発生している
                // -> 再度ログイン画面を表示
                return 'login';
            }
    
            // エラーが無ければnullを返す(引き続いてperform()メソッドが実行される
            return null;
        }
    ...
    }

要するに、アクションフォーム(アクションクラスのメンバ変数$action_formあるいは$afとして予め設定されています)のvalidate()メソッドを実行して、1以上の値が返されたら再度入力画面へ遷移すればよいだけです。

### (3) エラーメッセージを表示する

入力画面でエラーが発生したら、当然ですがエラーメッセージを表示させなければなりません。ここではその方法をご説明します。とはいっても、全てのエラーメッセージはSmartyの変数としてアサインされているので、単純にそれの値にアクセスすればよいだけです。

なお、ここで表示するエラーメッセージは勿論カスタマイズすることが出来ます。詳細は [エラーメッセージをカスタマイズする](form-message.md)を御覧下さい。

#### エラーメッセージ一覧

何は無くとも全てのエラーメッセージを表示させる場合は、$errors変数を利用します。以下はその典型的な例となります。

    {if count($errors)}
     <ul>
      {foreach from=$errors item=error}
       <li>{$error}</li>
      {/foreach}
     </ul>
    {/if}

#### 特定のフォームに対するエラーメッセージ

特定のフォームに対応するエラーメッセージを表示させるにはEthna組み込みのSmarty関数{message}を利用します。

引数$nameにフォーム名を指定することでフォーム名に対応するエラーメッセージ(無ければ空文字列)が表示されます。以下はその例です:

    <input type="text" name="mailaddress" value="{$form.mailaddress}">
    {message name="mailaddress"}

また、特定のフォームでエラーが発生しているかどうかを知るには同じくEthna組み込みのSmarty関数{is_error}を利用します。

    {if is_error('mailaddress')}
    エラー
    {/if}

### 補足

- 最近はフォーム属性をちまちま書くのすら面倒になってきました。もうちょっと楽できないものか考え中です
- アプリケーションでSmartyプラグインを追加することで以下のようにもうちょっと楽できます
  - エラーだったら<span class="error"></span>で自動で囲ったり、required属性が設定されていたら自動で「(\*)」を表示させたりするプラグインを書くことで、より楽をすることも出来ます(僕はしています)
  - ついでに<input>タグもある程度自動で出力するプラグインを書くとさらに楽です(こちらはEthna組み込みで提供したいなー、と思っています。JavaScriptコードも自動生成する機能とかもつけて)


* * *
\*1エラー処理の詳細については [エラー処理ポリシー](error-policy.md)等を参照してください  
\*2 [アプリケーション構築手順(3)](tutorial-practice3.md#content_1_4)も参照してください  
\*3例えば、入力値が必須で合った場合のエラーメッセージはテキストボックスなら「入力してください」、セレクトボックスなら「選択してください」というように振り分ける  
\*4この件は、代替案がPHP 5.2.6の時点では見つかっていないことから、「仕様」としてプロジェクトとしてはWONTFIX(修正しない) 方針です。代替案の提案がある方は、 [IRCやメーリングリスト](ethna-community.html) でお願いします。  
\*5UTF-8 な文字列であれば、mbregexp を使わずに、/^ほげ$/u としてもマルチバイト対応の正規表現チェックが利用できます。  

