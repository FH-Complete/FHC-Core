<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:mml="http://www.w3.org/1998/Math/MathML">

<!--
$Id: ctop.xsl,v 1.3 2002/09/20 08:41:39 davidc Exp $

Copyright David Carlisle 2001, 2002.

Use and distribution of this code are permitted under the terms of the <a
href="http://www.w3.org/Consortium/Legal/copyright-software-19980720"
>W3C Software Notice and License</a>.
-->

<xsl:output method="xml"/>

<xsl:template mode="c2p" match="*">
<xsl:copy>
  <xsl:copy-of select="@*"/>
  <xsl:apply-templates mode="c2p"/>
</xsl:copy>
</xsl:template>


<!-- 4.4.1.1 cn -->

<xsl:template mode="c2p" match="mml:cn">
 <mml:mn><xsl:apply-templates mode="c2p"/></mml:mn>
</xsl:template>

<xsl:template mode="c2p" match="mml:cn[@type='complex-cartesian']">
  <mml:mrow>
    <mml:mn><xsl:apply-templates mode="c2p" select="text()[1]"/></mml:mn>
    <mml:mo>+</mml:mo>
    <mml:mn><xsl:apply-templates mode="c2p" select="text()[2]"/></mml:mn>
    <mml:mo><!--&#8290;--><!--invisible times--></mml:mo>
    <mml:mi>i<!-- imaginary i --></mml:mi>
  </mml:mrow>
</xsl:template>

<xsl:template mode="c2p" match="mml:cn[@type='rational']">
  <mml:mrow>
    <mml:mn><xsl:apply-templates mode="c2p" select="text()[1]"/></mml:mn>
    <mml:mo>/</mml:mo>
    <mml:mn><xsl:apply-templates mode="c2p" select="text()[2]"/></mml:mn>
  </mml:mrow>
</xsl:template>

<xsl:template mode="c2p" match="mml:cn[@type='integer']">
  <xsl:choose>
  <xsl:when test="not(@base) or @base=10">
       <mml:mn><xsl:apply-templates mode="c2p"/></mml:mn>
  </xsl:when>
  <xsl:otherwise>
  <mml:msub>
    <mml:mn><xsl:apply-templates mode="c2p"/></mml:mn>
    <mml:mn><xsl:value-of select="@base"/></mml:mn>
  </mml:msub>
  </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template mode="c2p" match="mml:cn[@type='complex-polar']">
  <mml:mrow>
    <mml:mn><xsl:apply-templates mode="c2p" select="text()[1]"/></mml:mn>
    <mml:mo><!--&#8290;--><!--invisible times--></mml:mo>
    <mml:msup>
    <mml:mi>e<!-- exponential e--></mml:mi>
    <mml:mrow>
     <mml:mi>i<!-- imaginary i--></mml:mi>
     <mml:mo><!--&#8290;--><!--invisible times--></mml:mo>
     <mml:mn><xsl:apply-templates mode="c2p" select="text()[2]"/></mml:mn>
    </mml:mrow>
    </mml:msup>
  </mml:mrow>
</xsl:template>

<xsl:template mode="c2p" match="mml:cn[@type='e-notation']">
    <mml:mn><xsl:apply-templates mode="c2p" select="text()[1]"/>E<xsl:apply-templates mode="c2p" select="text()[2]"/></mml:mn>
</xsl:template>

<!-- 4.4.1.1 ci  -->

<xsl:template mode="c2p" match="mml:ci/text()">
 <mml:mi><xsl:value-of select="."/></mml:mi>
</xsl:template>

<xsl:template mode="c2p" match="mml:ci">
 <mml:mrow><xsl:apply-templates mode="c2p"/></mml:mrow>
</xsl:template>

<!-- 4.4.1.2 csymbol -->

<xsl:template mode="c2p" match="mml:csymbol/text()">
 <mml:mo><xsl:apply-templates mode="c2p"/></mml:mo>
</xsl:template>

<xsl:template mode="c2p" match="mml:csymbol">
 <mml:mrow><xsl:apply-templates mode="c2p"/></mml:mrow>
</xsl:template>

<!-- 4.4.2.1 apply 4.4.2.2 reln -->

<xsl:template mode="c2p" match="mml:apply|mml:reln">
 <mml:mrow>
 <xsl:apply-templates mode="c2p" select="*[1]">
  <xsl:with-param name="p" select="10"/>
 </xsl:apply-templates>
 <mml:mo><!--&#8290;--><!--invisible times--></mml:mo>
 <mml:mfenced open="(" close=")" separators=",">
 <xsl:apply-templates mode="c2p" select="*[position()&gt;1]"/>
 </mml:mfenced>
 </mml:mrow>
</xsl:template>

<!-- 4.4.2.3 fn -->
<xsl:template mode="c2p" match="mml:fn">
 <mml:mrow><xsl:apply-templates mode="c2p"/></mml:mrow>
</xsl:template>

<!-- 4.4.2.4 interval -->
<xsl:template mode="c2p" match="mml:interval[*[2]]">
 <mml:mfenced open="[" close="]"><xsl:apply-templates mode="c2p"/></mml:mfenced>
</xsl:template>
<xsl:template mode="c2p" match="mml:interval[*[2]][@closure='open']">
 <mml:mfenced open="(" close=")"><xsl:apply-templates mode="c2p"/></mml:mfenced>
</xsl:template>
<xsl:template mode="c2p" match="mml:interval[*[2]][@closure='open-closed']">
 <mml:mfenced open="(" close="]"><xsl:apply-templates mode="c2p"/></mml:mfenced>
</xsl:template>
<xsl:template mode="c2p" match="mml:interval[*[2]][@closure='closed-open']">
 <mml:mfenced open="[" close=")"><xsl:apply-templates mode="c2p"/></mml:mfenced>
</xsl:template>

<xsl:template mode="c2p" match="mml:interval">
 <mml:mfenced open="{{" close="}}"><xsl:apply-templates mode="c2p"/></mml:mfenced>
</xsl:template>

<!-- 4.4.2.5 inverse -->

<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:inverse]]">
 <mml:msup>
  <xsl:apply-templates mode="c2p" select="*[2]"/>
  <mml:mrow><mml:mo>(</mml:mo><mml:mn>-1</mml:mn><mml:mo>)</mml:mo></mml:mrow>
 </mml:msup>
</xsl:template>

<!-- 4.4.2.6 sep -->

<!-- 4.4.2.7 condition -->
<xsl:template mode="c2p" match="mml:condition">
 <mml:mrow><xsl:apply-templates mode="c2p"/></mml:mrow>
</xsl:template>

<!-- 4.4.2.8 declare -->
<xsl:template mode="c2p" match="mml:declare"/>

<!-- 4.4.2.9 lambda -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:lambda]]">
 <mml:mrow>
  <mml:mi>λ<!--lambda--></mml:mi>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:bvar/*"/></mml:mrow>
 <mml:mo>.</mml:mo>
 <mml:mfenced>
  <xsl:apply-templates mode="c2p" select="*[last()]"/>
 </mml:mfenced>
</mml:mrow>
</xsl:template>


<!-- 4.4.2.10 compose -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:compose]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>∘<!-- o --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>


<!-- 4.4.2.11` ident -->
<xsl:template mode="c2p" match="mml:ident">
<mml:mo>id</mml:mo>
</xsl:template>

<!-- 4.4.2.12` domain -->
<xsl:template mode="c2p" match="mml:domain">
<mml:mo>domain</mml:mo>
</xsl:template>

<!-- 4.4.2.13` codomain -->
<xsl:template mode="c2p" match="mml:codomain">
<mml:mo>codomain</mml:mo>
</xsl:template>

<!-- 4.4.2.14` image -->
<xsl:template mode="c2p" match="mml:image">
<mml:mo>image</mml:mo>
</xsl:template>

<!-- 4.4.2.15` domainofapplication -->
<xsl:template mode="c2p" match="mml:domainofapplication">
 <mml:error/>
</xsl:template>

<!-- 4.4.2.16` piecewise -->
<xsl:template mode="c2p" match="mml:piecewise">
<mml:mrow>
<mml:mo>{</mml:mo>
<mml:mtable>
 <xsl:for-each select="mml:piece|mml:otherwise">
 <mml:mtr>
 <mml:mtd><xsl:apply-templates mode="c2p" select="*[1]"/></mml:mtd>
 <mml:mtd><mml:mtext>  if  </mml:mtext></mml:mtd>
 <mml:mtd><xsl:apply-templates mode="c2p" select="*[2]"/></mml:mtd>
 </mml:mtr>
 </xsl:for-each>
</mml:mtable>
</mml:mrow>
</xsl:template>


<!-- 4.4.3.1 quotient -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:quotient]]">
<mml:mrow>
<mml:mo>⌊<!-- lfloor--></mml:mo>
<xsl:apply-templates mode="c2p" select="*[2]"/>
<mml:mo>/</mml:mo>
<xsl:apply-templates mode="c2p" select="*[3]"/>
<mml:mo>⌋<!-- rfloor--></mml:mo>
</mml:mrow>
</xsl:template>



<!-- 4.4.3.2 factorial -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:factorial]]">
<mml:mrow>
<xsl:apply-templates mode="c2p" select="*[2]">
  <xsl:with-param name="p" select="7"/>
</xsl:apply-templates>
<mml:mo>!</mml:mo>
</mml:mrow>
</xsl:template>


<!-- 4.4.3.3 divide -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:divide]]">
  <xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
  <xsl:with-param name="mo"><mml:mo>/</mml:mo></xsl:with-param>
  <xsl:with-param name="p" select="$p"/>
  <xsl:with-param name="this-p" select="3"/>
</xsl:call-template>
</xsl:template>


<!-- 4.4.3.4 max  min-->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:max]]">
<mml:mrow>
  <mml:mo>max</mml:mo>
  <xsl:call-template name="set"/>
</mml:mrow>
</xsl:template>

<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:min]]">
<mml:mrow>
  <mml:mo>max</mml:mo>
  <xsl:call-template name="set"/>
</mml:mrow>
</xsl:template>

<!-- 4.4.3.5  minus-->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:minus] and count(*)=2]">
<mml:mrow>
  <mml:mo>−<!--minus--></mml:mo>
  <xsl:apply-templates mode="c2p" select="*[2]">
      <xsl:with-param name="p" select="5"/>
  </xsl:apply-templates>
</mml:mrow>
</xsl:template>

<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:minus] and count(*)&gt;2]">
  <xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
  <xsl:with-param name="mo"><mml:mo>−<!--minus--></mml:mo></xsl:with-param>
  <xsl:with-param name="p" select="$p"/>
  <xsl:with-param name="this-p" select="2"/>
</xsl:call-template>
</xsl:template>

<!-- 4.4.3.6  plus-->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:plus]]">
  <xsl:param name="p" select="0"/>
  <mml:mrow>
  <xsl:if test="$p &gt; 2"><mml:mo>(</mml:mo></xsl:if>
  <xsl:for-each select="*[position()&gt;1]">
   <xsl:if test="position() &gt; 1">
    <mml:mo>
    <xsl:choose>
      <xsl:when test="self::mml:apply[*[1][self::mml:times] and       *[2][self::mml:apply/*[1][self::mml:minus] or self::mml:cn[not(mml:sep) and       (number(.) &lt; 0)]]]">−<!--minus--></xsl:when>
      <xsl:otherwise>+</xsl:otherwise>
    </xsl:choose>
    </mml:mo>
   </xsl:if>   
    <xsl:choose>
      <xsl:when test="self::mml:apply[*[1][self::mml:times] and       *[2][self::mml:cn[not(mml:sep) and (number(.) &lt;0)]]]">
     <mml:mrow>
     <mml:mn><xsl:value-of select="-(*[2])"/></mml:mn>
      <mml:mo><!--&#8290;--><!--invisible times--></mml:mo>
     <xsl:apply-templates mode="c2p" select=".">
     <xsl:with-param name="first" select="2"/>
     <xsl:with-param name="p" select="2"/>
   </xsl:apply-templates>
     </mml:mrow>
      </xsl:when>
      <xsl:when test="self::mml:apply[*[1][self::mml:times] and       *[2][self::mml:apply/*[1][self::mml:minus]]]">
     <mml:mrow>
     <xsl:apply-templates mode="c2p" select="./*[2]/*[2]"/>
     <xsl:apply-templates mode="c2p" select=".">
     <xsl:with-param name="first" select="2"/>
     <xsl:with-param name="p" select="2"/>
   </xsl:apply-templates>
     </mml:mrow>
      </xsl:when>
      <xsl:otherwise>
     <xsl:apply-templates mode="c2p" select=".">
     <xsl:with-param name="p" select="2"/>
   </xsl:apply-templates>
   </xsl:otherwise>
    </xsl:choose>
  </xsl:for-each>
  <xsl:if test="$p &gt; 2"><mml:mo>)</mml:mo></xsl:if>
  </mml:mrow>
</xsl:template>


<!-- 4.4.3.7 power -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:power]]">
<mml:msup>
<xsl:apply-templates mode="c2p" select="*[2]">
  <xsl:with-param name="p" select="5"/>
</xsl:apply-templates>
<xsl:apply-templates mode="c2p" select="*[3]">
  <xsl:with-param name="p" select="5"/>
</xsl:apply-templates>
</mml:msup>
</xsl:template>

<!-- 4.4.3.8 remainder -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:rem]]">
  <xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
  <xsl:with-param name="mo"><mml:mo>mod</mml:mo></xsl:with-param>
  <xsl:with-param name="p" select="$p"/>
  <xsl:with-param name="this-p" select="3"/>
</xsl:call-template>
</xsl:template>

<!-- 4.4.3.9  times-->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:times]]" name="times">
  <xsl:param name="p" select="0"/>
  <xsl:param name="first" select="1"/>
  <mml:mrow>
  <xsl:if test="$p &gt; 3"><mml:mo>(</mml:mo></xsl:if>
  <xsl:for-each select="*[position()&gt;1]">
   <xsl:if test="position() &gt; 1">
    <mml:mo>
    <xsl:choose>
      <xsl:when test="self::mml:cn">×<!-- times --></xsl:when>
      <xsl:otherwise><!--&#8290;--><!--invisible times--></xsl:otherwise>
    </xsl:choose>
    </mml:mo>
   </xsl:if> 
   <xsl:if test="position()&gt;= $first">
   <xsl:apply-templates mode="c2p" select=".">
     <xsl:with-param name="p" select="3"/>
   </xsl:apply-templates>
   </xsl:if>
  </xsl:for-each>
  <xsl:if test="$p &gt; 3"><mml:mo>)</mml:mo></xsl:if>
  </mml:mrow>
</xsl:template>


<!-- 4.4.3.10 root -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:root] and not(mml:degree) or mml:degree=2]" priority="4">
<mml:msqrt>
<xsl:apply-templates mode="c2p" select="*[position()&gt;1]"/>
</mml:msqrt>
</xsl:template>

<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:root]]">
<mml:mroot>
<xsl:apply-templates mode="c2p" select="*[position()&gt;1 and not(self::mml:degree)]"/>
<mml:mrow><xsl:apply-templates mode="c2p" select="mml:degree/*"/></mml:mrow>
</mml:mroot>
</xsl:template>

<!-- 4.4.3.11 gcd -->
<xsl:template mode="c2p" match="mml:gcd">
<mml:mo>gcd</mml:mo>
</xsl:template>

<!-- 4.4.3.12 and -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:and]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>∧<!-- and --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>


<!-- 4.4.3.13 or -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:or]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="3"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>∨<!-- or --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.3.14 xor -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:xor]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="3"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>xor</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>


<!-- 4.4.3.15 not -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:not]]">
<mml:mrow>
<mml:mo>¬<!-- not --></mml:mo>
<xsl:apply-templates mode="c2p" select="*[2]">
  <xsl:with-param name="p" select="7"/>
</xsl:apply-templates>
</mml:mrow>
</xsl:template>




<!-- 4.4.3.16 implies -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:implies]]">
  <xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
  <xsl:with-param name="mo"><mml:mo>⇒<!-- Rightarrow --></mml:mo></xsl:with-param>
  <xsl:with-param name="p" select="$p"/>
  <xsl:with-param name="this-p" select="3"/>
</xsl:call-template>
</xsl:template>


<!-- 4.4.3.17 forall -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:forall]]">
 <mml:mrow>
  <mml:mi>∀<!--forall--></mml:mi>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:bvar[not(current()/mml:condition)]/*|mml:condition/*"/></mml:mrow>
 <mml:mo>.</mml:mo>
 <mml:mfenced>
  <xsl:apply-templates mode="c2p" select="*[last()]"/>
 </mml:mfenced>
</mml:mrow>
</xsl:template>



<!-- 4.4.3.18 exists -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:exists]]">
 <mml:mrow>
  <mml:mi>∃<!--exists--></mml:mi>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:bvar[not(current()/mml:condition)]/*|mml:condition/*"/></mml:mrow>
 <mml:mo>.</mml:mo>
 <mml:mfenced>
  <xsl:apply-templates mode="c2p" select="*[last()]"/>
 </mml:mfenced>
</mml:mrow>
</xsl:template>


<!-- 4.4.3.19 abs -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:abs]]">
<mml:mrow>
<mml:mo>|</mml:mo>
<xsl:apply-templates mode="c2p" select="*[2]"/>
<mml:mo>|</mml:mo>
</mml:mrow>
</xsl:template>



<!-- 4.4.3.20 conjugate -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:conjugate]]">
<mml:mover>
<xsl:apply-templates mode="c2p" select="*[2]"/>
<mml:mo>¯<!-- overline --></mml:mo>
</mml:mover>
</xsl:template>

<!-- 4.4.3.21 arg -->
<xsl:template mode="c2p" match="mml:arg">
 <mml:mo>arg</mml:mo>
</xsl:template>


<!-- 4.4.3.22 real -->
<xsl:template mode="c2p" match="mml:real">
 <mml:mo>ℛ<!-- real --></mml:mo>
</xsl:template>

<!-- 4.4.3.23 imaginary -->
<xsl:template mode="c2p" match="mml:imaginary">
 <mml:mo>ℑ<!-- imaginary --></mml:mo>
</xsl:template>

<!-- 4.4.3.24 lcm -->
<xsl:template mode="c2p" match="mml:lcm">
 <mml:mo>lcm</mml:mo>
</xsl:template>


<!-- 4.4.3.25 floor -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:floor]]">
<mml:mrow>
<mml:mo>⌊<!-- lfloor--></mml:mo>
<xsl:apply-templates mode="c2p" select="*[2]"/>
<mml:mo>⌋<!-- rfloor--></mml:mo>
</mml:mrow>
</xsl:template>


<!-- 4.4.3.25 ceiling -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:ceiling]]">
<mml:mrow>
<mml:mo>⌈<!-- lceil--></mml:mo>
<xsl:apply-templates mode="c2p" select="*[2]"/>
<mml:mo>⌉<!-- rceil--></mml:mo>
</mml:mrow>
</xsl:template>

<!-- 4.4.4.1 eq -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:eq]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>=</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.4.2 neq -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:neq]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>≠<!-- neq --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.4.3 eq -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:gt]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>&gt;</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.4.4 lt -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:lt]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>&lt;</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.4.5 geq -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:geq]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>≥</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.4.6 geq -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:leq]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>≤</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.4.7 equivalent -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:equivalent]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>≡</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.4.8 approx -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:approx]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="1"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>≃</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>


<!-- 4.4.4.9 factorof -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:factorof]]">
  <xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
  <xsl:with-param name="mo"><mml:mo>|</mml:mo></xsl:with-param>
  <xsl:with-param name="p" select="$p"/>
  <xsl:with-param name="this-p" select="3"/>
</xsl:call-template>
</xsl:template>

<!-- 4.4.5.1 int -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:int]]">
 <mml:mrow>
 <mml:msubsup>
  <mml:mi>∫<!--int--></mml:mi>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:lowlimit/*|mml:interval/*[1]|mml:condition/*"/></mml:mrow>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:uplimit/*|mml:interval/*[2]"/></mml:mrow>
 </mml:msubsup>
 <xsl:apply-templates mode="c2p" select="*[last()]"/>
 <mml:mo>d</mml:mo><xsl:apply-templates mode="c2p" select="mml:bvar"/>
</mml:mrow>
</xsl:template>

<!-- 4.4.5.2 diff -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:diff] and mml:ci and count(*)=2]" priority="2">
 <mml:msup>
 <mml:mrow><xsl:apply-templates mode="c2p" select="*[2]"/></mml:mrow>
 <mml:mo>′<!--prime--></mml:mo>
 </mml:msup>
</xsl:template>

<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:diff]]" priority="1">
 <mml:mfrac>
 <xsl:choose>
 <xsl:when test="mml:bvar/mml:degree">
 <mml:mrow><mml:msup><mml:mo>d</mml:mo><xsl:apply-templates mode="c2p" select="mml:bvar/mml:degree/node()"/></mml:msup>
     <xsl:apply-templates mode="c2p" select="*[last()]"/></mml:mrow>
 <mml:mrow><mml:mo>d</mml:mo><mml:msup><xsl:apply-templates mode="c2p" select="mml:bvar/node()"/><xsl:apply-templates mode="c2p" select="mml:bvar/mml:degree/node()"/></mml:msup>
</mml:mrow>
</xsl:when>
<xsl:otherwise>
 <mml:mrow><mml:mo>d</mml:mo><xsl:apply-templates mode="c2p" select="*[last()]"/></mml:mrow>
 <mml:mrow><mml:mo>d</mml:mo><xsl:apply-templates mode="c2p" select="mml:bvar"/></mml:mrow>
</xsl:otherwise>
 </xsl:choose>
 </mml:mfrac>
</xsl:template>


<!-- 4.4.5.3 partialdiff -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:partialdiff] and mml:list and mml:ci and count(*)=3]" priority="2">
<mml:mrow>
 <mml:msub><mml:mo>D</mml:mo><mml:mrow>
<xsl:for-each select="mml:list[1]/*">
<xsl:apply-templates mode="c2p" select="."/>
<xsl:if test="position()&lt;last()"><mml:mo>,</mml:mo></xsl:if>
</xsl:for-each>
</mml:mrow></mml:msub>
 <mml:mrow><xsl:apply-templates mode="c2p" select="*[3]"/></mml:mrow>
</mml:mrow>
</xsl:template>

<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:partialdiff]]" priority="1">
 <mml:mfrac>
 <mml:mrow><mml:msup><mml:mo>∂<!-- partial --></mml:mo>
<mml:mrow>
 <xsl:choose>
 <xsl:when test="mml:degree">
<xsl:apply-templates mode="c2p" select="mml:degree/node()"/>
</xsl:when>
<xsl:when test="mml:bvar/mml:degree[string(number(.))='NaN']">
<xsl:for-each select="mml:bvar/mml:degree">
<xsl:apply-templates mode="c2p" select="node()"/>
<xsl:if test="position()&lt;last()"><mml:mo>+</mml:mo></xsl:if>
</xsl:for-each>
<xsl:if test="count(mml:bvar[not(mml:degree)])&gt;0">
<mml:mo>+</mml:mo><mml:mn><xsl:value-of select="count(mml:bvar[not(mml:degree)])"/></mml:mn>
</xsl:if>
</xsl:when>
<xsl:otherwise>
<mml:mn><xsl:value-of select="sum(mml:bvar/mml:degree)+count(mml:bvar[not(mml:degree)])"/></mml:mn>
</xsl:otherwise>
 </xsl:choose>
</mml:mrow>
</mml:msup>
     <xsl:apply-templates mode="c2p" select="*[last()]"/></mml:mrow>
<mml:mrow>
<xsl:for-each select="mml:bvar">
<mml:mrow>
<mml:mo>∂<!-- partial --></mml:mo><mml:msup><xsl:apply-templates mode="c2p" select="node()"/>
                     <mml:mrow><xsl:apply-templates mode="c2p" select="mml:degree/node()"/></mml:mrow>
</mml:msup>
</mml:mrow>
</xsl:for-each>
</mml:mrow>
 </mml:mfrac>
</xsl:template>

<!-- 4.4.5.4  lowlimit-->
<xsl:template mode="c2p" match="mml:lowlimit"/>

<!-- 4.4.5.5 uplimit-->
<xsl:template mode="c2p" match="mml:uplimit"/>

<!-- 4.4.5.6  bvar-->
<xsl:template mode="c2p" match="mml:bvar">
 <mml:mi><xsl:apply-templates mode="c2p"/></mml:mi>
 <xsl:if test="following-sibling::mml:bvar"><mml:mo>,</mml:mo></xsl:if>
</xsl:template>

<!-- 4.4.5.7 degree-->
<xsl:template mode="c2p" match="mml:degree"/>

<!-- 4.4.5.8 divergence-->
<xsl:template mode="c2p" match="mml:divergence">
<mml:mo>div</mml:mo>
</xsl:template>

<!-- 4.4.5.9 grad-->
<xsl:template mode="c2p" match="mml:grad">
<mml:mo>grad</mml:mo>
</xsl:template>

<!-- 4.4.5.10 curl -->
<xsl:template mode="c2p" match="mml:curl">
<mml:mo>curl</mml:mo>
</xsl:template>


<!-- 4.4.5.11 laplacian-->
<xsl:template mode="c2p" match="mml:laplacian">
<mml:msup><mml:mo>∇<!-- nabla --></mml:mo><mml:mn>2</mml:mn></mml:msup>
</xsl:template>

<!-- 4.4.6.1 set -->

<xsl:template mode="c2p" match="mml:set">
  <xsl:call-template name="set"/>
</xsl:template>

<!-- 4.4.6.2 list -->

<xsl:template mode="c2p" match="mml:list">
  <xsl:call-template name="set">
   <xsl:with-param name="o" select="'('"/>
   <xsl:with-param name="c" select="')'"/>
  </xsl:call-template>
</xsl:template>

<!-- 4.4.6.3 union -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:union]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>∪<!-- union --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.4 intersect -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:intersect]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="3"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>∩<!-- intersect --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.5 in -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:in]]">
  <xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
  <xsl:with-param name="mo"><mml:mo>∈<!-- in --></mml:mo></xsl:with-param>
  <xsl:with-param name="p" select="$p"/>
  <xsl:with-param name="this-p" select="3"/>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.5 notin -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:notin]]">
  <xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
  <xsl:with-param name="mo"><mml:mo>∉<!-- not in --></mml:mo></xsl:with-param>
  <xsl:with-param name="p" select="$p"/>
  <xsl:with-param name="this-p" select="3"/>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.7 subset -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:subset]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>⊆<!-- subseteq --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.8 prsubset -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:prsubset]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>⊂<!-- prsubset --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.9 notsubset -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:notsubset]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>⊈<!-- notsubseteq --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.10 notprsubset -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:notprsubset]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>⊄<!-- prsubset --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.11 setdiff -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:setdiff]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="binary">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>∖<!-- setminus --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.6.12 card -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:card]]">
<mml:mrow>
<mml:mo>|</mml:mo>
<xsl:apply-templates mode="c2p" select="*[2]"/>
<mml:mo>|</mml:mo>
</mml:mrow>
</xsl:template>

<!-- 4.4.6.13 cartesianproduct -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:cartesianproduct or self::mml:vectorproduct]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>×<!-- times --></mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<xsl:template match="mml:apply[*[1][self::mml:cartesianproduct][count(following-sibling::mml:reals)=count(following-sibling::*)]]" priority="2">
<mml:msup>
<xsl:apply-templates mode="c2p" select="*[2]">
  <xsl:with-param name="p" select="5"/>
</xsl:apply-templates>
<mml:mn><xsl:value-of select="count(*)-1"/></mml:mn>
</mml:msup>
</xsl:template>


<!-- 4.4.7.1 sum -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:sum]]">
 <mml:mrow>
 <mml:msubsup>
  <mml:mo>∑<!--sum--></mml:mo>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:lowlimit/*|mml:interval/*[1]|mml:condition/*"/></mml:mrow>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:uplimit/*|mml:interval/*[2]"/></mml:mrow>
 </mml:msubsup>
 <xsl:apply-templates mode="c2p" select="*[last()]"/>
</mml:mrow>
</xsl:template>

<!-- 4.4.7.2 product -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:product]]">
 <mml:mrow>
 <mml:msubsup>
  <mml:mo>∏<!--product--></mml:mo>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:lowlimit/*|mml:interval/*[1]|mml:condition/*"/></mml:mrow>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:uplimit/*|mml:interval/*[2]"/></mml:mrow>
 </mml:msubsup>
 <xsl:apply-templates mode="c2p" select="*[last()]"/>
</mml:mrow>
</xsl:template>

<!-- 4.4.7.3 limit -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:limit]]">
 <mml:mrow>
 <mml:munder>
  <mml:mi>limit</mml:mi>
 <mml:mrow><xsl:apply-templates mode="c2p" select="mml:lowlimit|mml:condition/*"/></mml:mrow>
 </mml:munder>
 <xsl:apply-templates mode="c2p" select="*[last()]"/>
</mml:mrow>
</xsl:template>

<xsl:template mode="c2p" match="mml:apply[mml:limit]/mml:lowlimit" priority="3">
<mml:mrow>
<xsl:apply-templates mode="c2p" select="../mml:bvar/node()"/>
<mml:mo>→<!--rightarrow--></mml:mo>
<xsl:apply-templates mode="c2p"/>
</mml:mrow>
</xsl:template>


<!-- 4.4.7.4 tendsto -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:tendsto]]">
 <xsl:param name="p"/>
<xsl:call-template name="binary">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>
  <xsl:choose>
   <xsl:when test="@type='above'">↘<!--searrow--></xsl:when>
   <xsl:when test="@type='below'">↗<!--nearrow--></xsl:when>
   <xsl:when test="@type='two-sided'">→<!--rightarrow--></xsl:when>
   <xsl:otherwise>→<!--rightarrow--></xsl:otherwise>
  </xsl:choose>
  </mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.8.1 trig -->
<xsl:template mode="c2p" match="mml:apply[*[1][  self::mml:sin or self::mml:cos or self::mml:tan or self::mml:sec or  self::mml:csc or self::mml:cot or self::mml:sinh or self::mml:cosh or  self::mml:tanh or self::mml:sech or self::mml:csch or self::mml:coth or  self::mml:arcsin or self::mml:arccos or self::mml:arctan or self::mml:arccosh  or self::mml:arccot or self::mml:arccoth or self::mml:arccsc or  self::mml:arccsch or self::mml:arcsec or self::mml:arcsech or  self::mml:arcsinh or self::mml:arctanh or self::mml:ln]]">
<mml:mrow>
<mml:mi><xsl:value-of select="local-name(*[1])"/></mml:mi>
<xsl:apply-templates mode="c2p" select="*[2]">
  <xsl:with-param name="p" select="7"/>
</xsl:apply-templates>
</mml:mrow>
</xsl:template>




<!-- 4.4.8.2 exp -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:exp]]">
<mml:msup>
<mml:mi>e<!-- exponential e--></mml:mi>
<mml:mrow><xsl:apply-templates mode="c2p" select="*[2]"/></mml:mrow>
</mml:msup>
</xsl:template>

<!-- 4.4.8.3 ln -->
<!-- with trig -->

<!-- 4.4.8.4 log -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:log]]">
<mml:mrow>
<xsl:choose>
<xsl:when test="not(mml:logbase) or mml:logbase=10">
<mml:mi>log</mml:mi>
</xsl:when>
<xsl:otherwise>
<mml:msub>
<mml:mi>log</mml:mi>
<mml:mrow><xsl:apply-templates mode="c2p" select="mml:logbase/node()"/></mml:mrow>
</mml:msub>
</xsl:otherwise>
</xsl:choose>
<xsl:apply-templates mode="c2p" select="*[last()]">
  <xsl:with-param name="p" select="7"/>
</xsl:apply-templates>
</mml:mrow>
</xsl:template>


<!-- 4.4.9.1 mean -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:mean]]">
<mml:mrow>
 <mml:mo>〈<!--langle--></mml:mo>
    <xsl:for-each select="*[position()&gt;1]">
      <xsl:apply-templates mode="c2p" select="."/>
      <xsl:if test="position() !=last()"><mml:mo>,</mml:mo></xsl:if>
    </xsl:for-each>
<mml:mo>〉<!--rangle--></mml:mo>
</mml:mrow>
</xsl:template>


<!-- 4.4.9.2 sdef -->
<xsl:template mode="c2p" match="mml:sdev">
<mml:mo>σ<!--sigma--></mml:mo>
</xsl:template>

<!-- 4.4.9.3 variance -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:variance]]">
<mml:msup>
<mml:mrow>
<mml:mo>σ<!--sigma--></mml:mo>
<mml:mo>(</mml:mo>
<xsl:apply-templates mode="c2p" select="*[2]"/>
<mml:mo>)</mml:mo>
</mml:mrow>
<mml:mn>2</mml:mn>
</mml:msup>
</xsl:template>


<!-- 4.4.9.4 median -->
<xsl:template mode="c2p" match="mml:median">
<mml:mo>median</mml:mo>
</xsl:template>


<!-- 4.4.9.5 mode -->
<xsl:template mode="c2p" match="mml:mode">
<mml:mo>mode</mml:mo>
</xsl:template>

<!-- 4.4.9.5 moment -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:moment]]">
<mml:mrow>
 <mml:mo>〈<!--langle--></mml:mo>
       <mml:msup>
      <xsl:apply-templates mode="c2p" select="*[last()]"/>
      <mml:mrow><xsl:apply-templates mode="c2p" select="mml:degree/node()"/></mml:mrow>
       </mml:msup>
<mml:mo>〉<!--rangle--></mml:mo>
</mml:mrow>
</xsl:template>

<!-- 4.4.9.5 momentabout -->
<xsl:template mode="c2p" match="mml:momentabout"/>

<!-- 4.4.10.1 vector  -->
<xsl:template mode="c2p" match="mml:vector">
<mml:mrow>
<mml:mo>(</mml:mo>
<mml:mtable>
<xsl:for-each select="*">
<mml:mtr><mml:mtd><xsl:apply-templates mode="c2p" select="."/></mml:mtd></mml:mtr>
</xsl:for-each>
</mml:mtable>
<mml:mo>)</mml:mo>
</mml:mrow>
</xsl:template>

<!-- 4.4.10.2 matrix  -->
<xsl:template mode="c2p" match="mml:matrix">
<mml:mrow>
<mml:mo>(</mml:mo>
<mml:mtable>
<xsl:apply-templates mode="c2p"/>
</mml:mtable>
<mml:mo>)</mml:mo>
</mml:mrow>
</xsl:template>

<!-- 4.4.10.3 matrixrow  -->
<xsl:template mode="c2p" match="mml:matrixrow">
<mml:mtr>
<xsl:for-each select="*">
<mml:mtd><xsl:apply-templates mode="c2p" select="."/></mml:mtd>
</xsl:for-each>
</mml:mtr>
</xsl:template>

<!-- 4.4.10.4 determinant  -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:determinant]]">
<mml:mrow>
<mml:mi>det</mml:mi>
<xsl:apply-templates mode="c2p" select="*[2]">
  <xsl:with-param name="p" select="7"/>
</xsl:apply-templates>
</mml:mrow>
</xsl:template>

<xsl:template match="mml:apply[*[1][self::mml:determinant]][*[2][self::mml:matrix]]" priority="2">
<mml:mrow>
<mml:mo>|</mml:mo>
<mml:mtable>
<xsl:apply-templates mode="c2p" select="mml:matrix/*"/>
</mml:mtable>
<mml:mo>|</mml:mo>
</mml:mrow>
</xsl:template>

<!-- 4.4.10.5 transpose -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:transpose]]">
<mml:msup>
<xsl:apply-templates mode="c2p" select="*[2]">
  <xsl:with-param name="p" select="7"/>
</xsl:apply-templates>
<mml:mi>T</mml:mi>
</mml:msup>
</xsl:template>

<!-- 4.4.10.5 selector -->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:selector]]">
<mml:msub>
<xsl:apply-templates mode="c2p" select="*[2]">
  <xsl:with-param name="p" select="7"/>
</xsl:apply-templates>
<mml:mrow>
    <xsl:for-each select="*[position()&gt;2]">
      <xsl:apply-templates mode="c2p" select="."/>
      <xsl:if test="position() !=last()"><mml:mo>,</mml:mo></xsl:if>
    </xsl:for-each>
</mml:mrow>
</mml:msub>
</xsl:template>

<!-- *** -->
<!-- 4.4.10.6 vectorproduct see cartesianproduct -->


<!-- 4.4.10.7 scalarproduct-->
<xsl:template mode="c2p" match="mml:apply[*[1][self::mml:scalarproduct or self::mml:outerproduct]]">
<xsl:param name="p" select="0"/>
<xsl:call-template name="infix">
 <xsl:with-param name="this-p" select="2"/>
 <xsl:with-param name="p" select="$p"/>
 <xsl:with-param name="mo"><mml:mo>.</mml:mo></xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- 4.4.10.8 outerproduct-->

<!-- 4.4.11.2 semantics -->
<xsl:template mode="c2p" match="mml:semantics">
 <xsl:apply-templates mode="c2p" select="*[1]"/>
</xsl:template>
<xsl:template mode="c2p" match="mml:semantics[mml:annotation-xml/@encoding='MathML-Presentation']">
 <xsl:apply-templates mode="c2p" select="mml:annotation-xml[@encoding='MathML-Presentation']/node()"/>
</xsl:template>

<!-- 4.4.12.1 integers -->
<xsl:template mode="c2p" match="mml:integers">
<mml:mi mathvariant="double-struck">Z</mml:mi>
</xsl:template>

<!-- 4.4.12.2 reals -->
<xsl:template mode="c2p" match="mml:reals">
<mml:mi mathvariant="double-struck">R</mml:mi>
</xsl:template>

<!-- 4.4.12.3 rationals -->
<xsl:template mode="c2p" match="mml:rationals">
<mml:mi mathvariant="double-struck">Q</mml:mi>
</xsl:template>

<!-- 4.4.12.4 naturalnumbers -->
<xsl:template mode="c2p" match="mml:naturalnumbers">
<mml:mi mathvariant="double-struck">N</mml:mi>
</xsl:template>

<!-- 4.4.12.5 complexes -->
<xsl:template mode="c2p" match="mml:complexes">
<mml:mi mathvariant="double-struck">C</mml:mi>
</xsl:template>

<!-- 4.4.12.6 primes -->
<xsl:template mode="c2p" match="mml:primes">
<mml:mi mathvariant="double-struck">P</mml:mi>
</xsl:template>

<!-- 4.4.12.7 exponentiale -->
<xsl:template mode="c2p" match="mml:exponentiale">
  <mml:mi>e<!-- exponential e--></mml:mi>
</xsl:template>

<!-- 4.4.12.8 imaginaryi -->
<xsl:template mode="c2p" match="mml:imaginaryi">
  <mml:mi>i<!-- imaginary i--></mml:mi>
</xsl:template>

<!-- 4.4.12.9 notanumber -->
<xsl:template mode="c2p" match="mml:notanumber">
  <mml:mi>NaN</mml:mi>
</xsl:template>

<!-- 4.4.12.10 true -->
<xsl:template mode="c2p" match="mml:true">
  <mml:mi>true</mml:mi>
</xsl:template>

<!-- 4.4.12.11 false -->
<xsl:template mode="c2p" match="mml:false">
  <mml:mi>false</mml:mi>
</xsl:template>

<!-- 4.4.12.12 emptyset -->
<xsl:template mode="c2p" match="mml:emptyset">
  <mml:mi>∅<!-- emptyset --></mml:mi>
</xsl:template>


<!-- 4.4.12.13 pi -->
<xsl:template mode="c2p" match="mml:pi">
  <mml:mi>π<!-- pi --></mml:mi>
</xsl:template>

<!-- 4.4.12.14 eulergamma -->
<xsl:template mode="c2p" match="mml:eulergamma">
  <mml:mi>γ<!-- gamma --></mml:mi>
</xsl:template>

<!-- 4.4.12.15 infinity -->
<xsl:template mode="c2p" match="mml:infinity">
  <mml:mi>∞<!-- infinity --></mml:mi>
</xsl:template>


<!-- ****************************** -->
<xsl:template name="infix">
  <xsl:param name="mo"/>
  <xsl:param name="p" select="0"/>
  <xsl:param name="this-p" select="0"/>
  <mml:mrow>
  <xsl:if test="$this-p &lt; $p"><mml:mo>(</mml:mo></xsl:if>
  <xsl:for-each select="*[position()&gt;1]">
   <xsl:if test="position() &gt; 1">
    <xsl:copy-of select="$mo"/>
   </xsl:if>   
   <xsl:apply-templates mode="c2p" select=".">
     <xsl:with-param name="p" select="$this-p"/>
   </xsl:apply-templates>
  </xsl:for-each>
  <xsl:if test="$this-p &lt; $p"><mml:mo>)</mml:mo></xsl:if>
  </mml:mrow>
</xsl:template>

<xsl:template name="binary">
  <xsl:param name="mo"/>
  <xsl:param name="p" select="0"/>
  <xsl:param name="this-p" select="0"/>
  <mml:mrow>
  <xsl:if test="$this-p &lt; $p"><mml:mo>(</mml:mo></xsl:if>
   <xsl:apply-templates mode="c2p" select="*[2]">
     <xsl:with-param name="p" select="$this-p"/>
   </xsl:apply-templates>
   <xsl:copy-of select="$mo"/>
   <xsl:apply-templates mode="c2p" select="*[3]">
     <xsl:with-param name="p" select="$this-p"/>
   </xsl:apply-templates>
  <xsl:if test="$this-p &lt; $p"><mml:mo>)</mml:mo></xsl:if>
  </mml:mrow>
</xsl:template>

<xsl:template name="set">
  <xsl:param name="o" select="'{'"/>
  <xsl:param name="c" select="'}'"/>
  <mml:mrow>
   <mml:mo><xsl:value-of select="$o"/></mml:mo>
   <xsl:choose>
   <xsl:when test="mml:condition">
   <mml:mrow><xsl:apply-templates mode="c2p" select="mml:bvar/*[not(self::bvar or self::condition)]"/></mml:mrow>
   <mml:mo>|</mml:mo>
   <mml:mrow><xsl:apply-templates mode="c2p" select="mml:condition/node()"/></mml:mrow>
   </xsl:when>
   <xsl:otherwise>
    <xsl:for-each select="*">
      <xsl:apply-templates mode="c2p" select="."/>
      <xsl:if test="position() !=last()"><mml:mo>,</mml:mo></xsl:if>
    </xsl:for-each>
   </xsl:otherwise>
   </xsl:choose>
   <mml:mo><xsl:value-of select="$c"/></mml:mo>
  </mml:mrow>
</xsl:template>

</xsl:stylesheet>